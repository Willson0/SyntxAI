<?php

namespace App\Http;


use App\Models\Admin;
use App\Models\AdminCookie;
use App\Models\Event;
use App\Models\Notification;
use App\Models\Picture;
use App\Models\Post;
use App\Models\Service;
use App\Models\User;
use Google\Cloud\Translate\V3\Client\TranslationServiceClient;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use OpenAI\Client;

class utils
{
    static public function sendMessage ($chat_id, $text) {
        $token = env("TELEGRAM_BOT_TOKEN"); // Токен бота
        $url = "https://api.telegram.org/bot$token/sendMessage";

        $response = Http::post($url, [
            'chat_id' => $chat_id,
            'text' => $text,
            "reply_markup" => [
                "remove_keyboard" => true
            ]
        ]);

        if ($response->ok()) return 1;
        else return 0;
    }

    static public function requestFullname ($chat_id) {
        $token = env("TELEGRAM_BOT_TOKEN");
        $url = "https://api.telegram.org/bot$token/sendMessage";

        Http::post($url, [
            'chat_id' => $chat_id,
            'text' => "Телефон успешно обновлен.",
            "reply_markup" => [
                "remove_keyboard" => true,
            ]
        ]);

        $response = Http::post($url, [
            'chat_id' => $chat_id,
            'text' => "Отправьте своё ФИО в формате 'Фамилия Имя Отчество'",
            "reply_markup" => [
                "inline_keyboard" => [
                    [
                        [
                            "text" => "Пропустить",
                            "callback_data" => "refuse_fullname"
                        ]
                    ]
                ],
            ]
        ]);

        if ($response->ok()) return 1;
        else return 0;
    }


    public static function isSafe(string $botToken, string $initData): bool
    {
        [$checksum, $sortedInitData] = self::convertInitData($initData);
        $secretKey                   = hash_hmac('sha256', $botToken, 'WebAppData', true);
        $hash                        = bin2hex(hash_hmac('sha256', $sortedInitData, $secretKey, true));

        return 0 === strcmp($hash, $checksum);
    }

    private static function convertInitData(string $initData): array
    {
        $initDataArray = explode('&', rawurldecode($initData));
        $needle        = 'hash=';
        $hash          = '';

        foreach ($initDataArray as &$data) {
            if (substr($data, 0, \strlen($needle)) === $needle) {
                $hash = substr_replace($data, '', 0, \strlen($needle));
                $data = null;
            }
        }
        $initDataArray = array_filter($initDataArray);
        sort($initDataArray);

        return [$hash, implode("\n", $initDataArray)];
    }

    public static function getSettings()
    {
        return json_decode(file_get_contents(storage_path('app/settings.json')), true);
    }

    public static function returnToAdmin ($menu, $user, $text) {
        $user->step = "admin_menu";
        $user->save();

        $keyboard = [];
        foreach ($menu["menu"] as $button) $keyboard[] = ["text" => $button["name"]];
        $keyboard = array_chunk($keyboard, 2);

        $token = env("TELEGRAM_BOT_TOKEN");

        $url = "https://api.telegram.org/bot$token/sendMessage";
        Http::post($url, [
            'chat_id' => $user->telegram_id,
            'text' => $text,
            "reply_markup" => [
                "keyboard" => $keyboard,
            ]
        ]);
    }

    public static function answerData ($text, $request, $user, $deleteMarkup = true) {
        $token = env("TELEGRAM_BOT_TOKEN"); // Токен бота
        $editurl = "https://api.telegram.org/bot$token/editMessageReplyMarkup";
        $url = "https://api.telegram.org/bot$token/answerCallbackQuery";

        Http::post($url, [
            "callback_query_id" => $request["callback_query"]["id"],
            'text' => $text,
        ]);

        if ($deleteMarkup)
            Http::post($editurl, [
                "chat_id" => $user->telegram_id,
                "message_id" => $request["callback_query"]["message"]["message_id"],
                "reply_markup" => [
                    "inline_keyboard" => [[]],
                ],
            ]);
    }
    static function gen_cookie ($user, $isadmin = false) {
        if ($isadmin) $cookieclass = AdminCookie::class;
        else $cookieclass = Cookie::class;

        do $cookie = self::gen_str(32);
        while ($cookieclass::where("cookie", $cookie)->exists());

        $cookieclass::create([
            "user_id" => $user->id,
            "cookie" => $cookie
        ]);
        return $cookie;
    }

    static public function gen_str ($length) {
        $permitted_chars = '0123456789abcdefghijklmnopqrstuvwxyz';
        $random_string = '';
        for($i = 0; $i < $length; $i++) {
            $random_character = $permitted_chars[mt_rand(0, strlen($permitted_chars) - 1)];
            $random_string .= $random_character;
        }
        return $random_string;
    }

    static function index ($class, $request, $forAdmin = false) {
        $limit = 10;
        if ($request->has("limit")) $limit = $request->limit;

        $query = $class::take($limit);
        if ($class === Post::class) $query = Post::limit($limit)->orderBy("created_at", "desc")->with("pictures")
            ->with("breed")->with("user")->with("city")->with("category");
        else if ($class === Service::class) $query = Service::limit($limit)->orderBy("created_at", "desc")
            ->with("pictures")->with("user")->with("city")->with("category");
        else if ($class === Event::class) $query = Event::limit($limit)->where("moderated", "1")->orderBy("created_at", "desc")
            ->with("pictures")->with("user")->with("city")->with("category");
        else if ($class === User::class) $query = User::limit($limit)->with('city');

        if ($request->has("sort")) $query->orderby("id", $request->sort);
        if ($request->has('datesort')) $query->orderby('id', $request->datesort);
        if ($request->has('offset')) $query->offset($request->offset);
        if ($request->has('namesort')) $query->orderby('title', $request->namesort);
        if ($request->has("category")) {
            if ($class === Service::class) $query->where("type_id", $request->category);
            else $query->where("category_id", $request->category);
        }
        if ($request->has("breed")) $query->where("breed_id", $request->breed);
        if ($request->has("gender")) $query->where("gender", $request->gender);
        if ($request->has("city")) $query->where("city_id", $request->city);
        if ($request->has("price_from") OR $request->has("price_to"))
            $query->whereBetween("price", [$request->price_from ?? 0, $request->price_to ?? 10000000]);
        if ($request->has("age_from") AND $request->has("age_to"))
            $query->whereBetween("age", [$request->age_from, $request->age_to]);
        if ($request->has("rating") AND ($request->rating === "true")) $query->where("rating", ">=", 4);
        if ($request->has("isNew") AND ($request->isNew === "true")) $query->orderBy("created_at", "desc");
            else $query->orderBy("created_at", "asc");
        if ($request->has("s")) {
            if ($class === User::class) $query->where("fullname", "like", "%$request->s%")->orWhere("username", "like", "%$request->s%");
            else $query->where("title", "like", "%$request->s%");
        }

        if ($forAdmin) {
            $countpage = ceil($query->count()/$limit);
            if ($request->has('page') and $limit) $query->skip(($request->page - 1) * $limit);

            $response["data"] = $query->get();
            $response["count"] = $countpage;

            return response()->json($response);
        }

        return $query->get();
    }

    static function sendAdmin ($message) {
        $admins = Admin::all();
        foreach ($admins as $admin) {
            $token = env("TELEGRAM_BOT_TOKEN"); // Токен бота
            $url = "https://api.telegram.org/bot$token/sendMessage";

            Http::post($url, [
                'chat_id' => $admin->telegram_id,
                'text' => $message,
                'parse_mode' => 'Markdown'
            ]);
        }
    }

    static function transliterate ($text) {
        $rus = [
            'А','Б','В','Г','Д','Е','Ё','Ж','З','И','Й',
            'К','Л','М','Н','О','П','Р','С','Т','У','Ф',
            'Х','Ц','Ч','Ш','Щ','Ъ','Ы','Ь','Э','Ю','Я',
            'а','б','в','г','д','е','ё','ж','з','и','й',
            'к','л','м','н','о','п','р','с','т','у','ф',
            'х','ц','ч','ш','щ','ъ','ы','ь','э','ю','я'
        ];

        $lat = [
            'A','B','V','G','D','E','E','Zh','Z','I','Y',
            'K','L','M','N','O','P','R','S','T','U','F',
            'H','Ts','Ch','Sh','Sch','','Y','','E','Yu','Ya',
            'a','b','v','g','d','e','e','zh','z','i','y',
            'k','l','m','n','o','p','r','s','t','u','f',
            'h','ts','ch','sh','sch','','y','','e','yu','ya'
        ];

        $text = str_replace($rus, $lat, $text);
        $text = preg_replace('/\s+/', '_', $text);
        $text = preg_replace('/[^A-Za-z0-9_]/', '', $text);
        $text = preg_replace('/_+/', '_', $text);
        $text = trim($text, '_');
        return $text;
    }

    static function addNotification ($user, $action, $type, $object) {
        $tables = [
            "post" => Post::class,
            "service" => Service::class,
            "event" => Event::class,
            "user" => User::class,
        ];
        $object = $tables[$type]::find($object);

        $title = "";
        Log::critical($object->toArray());
        if ($action == "favourite") {
            $object->get();

            $title = "Вашу услугу добавили в избранное";
            $description = "Пользователь " . ($object->user->fullname ?? $object->fullname) . " добавил услугу {$object->title} вашей собаки в избранное";
        } else if ($action == "subscribe") {
            $title = "На вас подписались";
            $description = "Пользователь {$object->fullname} подписался на вас";
        } else if ($action == "accept") {
            $title = "Ваше мероприятие одобрили";
            $description = "Администратор одобрил ваше мероприятие";
        }

        Notification::create([
            "user_id" => $object->user->id ?? $object->id,
            "title" => $title,
            "description" => $description,
            "type" => $type,
            "object_id" => $object->id,
            "readed" => false,
        ]);

        return true;
    }

    static function update ($post, $user, $request, $type = "post") {
        if ($post->user_id !== $user->id) abort (409);

        $data = $request->validated();
        $post->fill($data)->save();

        if ($request->has("delete_pictures"))
            foreach ($request->delete_pictures as $picture) Picture::destroy($picture);

        $time = time();
        $index = 0;
        $pictures = $request->file('pictures', []);

        foreach ($pictures as &$picture) {
            Storage::disk("public")->putFileAs($type, $picture, "image_$time" . $index . "." . $picture->extension());
            $picture = "$type/image_$time" . $index . "." . $picture->extension();
            $index++;
        }

        if ($request->has("number_main_picture") AND $request->number_main_picture < sizeof($pictures)) {
            $oldPictures = Picture::where("type", $type)->where("object_id", $post->id)->get();
            Picture::where("type", $type)->where("object_id", $post->id)->delete();

            Picture::create([
                "type" => $type,
                "object_id" => $post->id,
                "url" => $pictures[$request->number_main_picture],
            ]);
            unset($pictures[$request->number_main_picture]);

            foreach ($oldPictures as $oldPicture) {
                Picture::create([
                    "type" => $type,
                    "object_id" => $post->id,
                    "url" => $oldPicture->url,
                ]);
            }
        }

        foreach ($pictures as $picture) {
            Picture::create([
                "type" => $type,
                "object_id" => $post->id,
                "url" => $picture,
            ]);
        }

        return response()->json($post);
    }
}
