<script>
import TextModelsNavigationComponents from "@/components/TextModelsNavigationComponents.vue";

export default {
    name: "TextModelsView",
    components: {TextModelsNavigationComponents},
    created () {
        const link = document.createElement('link');
        link.rel = 'stylesheet';
        link.href = '/src/assets/TextModels.css';
        link.id = 'component-styles'; // Чтобы потом удалить
        document.head.appendChild(link);
    },
    beforeUnmount() {
        // Удаляем стили при уничтожении компонента
        const link = document.getElementById('component-styles');
        if (link) link.remove();
    },
    mounted () {

    },
    methods: {
        saveAndGo () {
            window.Telegram.WebApp.close();
        }
    }
}
</script>

<template>
    <div class="notification">
        <i class="bi bi-info-square-fill"></i> Устанавливайте различные модели, используйте GPTs, сохраняйте свои индивидуальные настройки и контролируйте запросы в этом меню.</div>

    <TextModelsNavigationComponents />

    <section class="box margin">
        <div class="statistics">
            <div class="stat-item">
                <div>
                    <span class="stat-icon"><i class="bi bi-star-fill"></i></span>
                    <span class="stat-text">Тарифный план</span>
                    <span class="stat-number">
                    💼 FREE                </span>
                </div>
            </div>
            <div class="stat-item">
                <div>
                    <span class="stat-icon"><i class="bi bi-person-workspace"></i></span>
                    <span class="stat-text">Доступно пробных запроса</span>
                    <span class="stat-number">5 ✏️</span>
                </div>
            </div>
        </div>
    </section>


    <section class="box margin" style="padding: 3px 10px;">
        <form class="settings-form" id="settings-form">
            <input type="hidden" id="user_id" value="1337592809">
            <div class="form-group">
                <div class="label-and-input">
                    <label for="model">
                        Модель                </label>
                    <div class="select-wrapper">
                        <select id="model" name="model" class="select-input" onchange="loadVersions(this.value)">
                            <option value="2"  disabled>🚧 GPT-o3 PRO</option>
                            <option value="5"  >GPT-o1</option>
                            <option value="6" selected >GPT-o1 Mini</option>
                            <option value="7"  >🏆 GPT-o3</option>
                            <option value="8"  >GPT-o3 Mini High</option>
                            <option value="9"  >♾️ GPT-o3 Mini</option>
                            <option value="10"  >🔥 GPT-o4 Mini</option>
                            <option value="13"  >🔥 GPT-4.1</option>
                            <option value="14"  >♾️ GPT-4.1 Mini</option>
                            <option value="15"  >♾️ GPT-4.1 Nano</option>
                            <option value="16"  >GPT-4.5</option>
                            <option value="21"  >👨‍🎨 GPT-4o-Images</option>
                            <option value="22"  >🏆 GPT-4o +Internet +File</option>
                            <option value="23"  >GPT-4o</option>
                            <option value="24"  >♾️ GPT-4o Mini</option>
                            <option value="25"  >GPT-4 Turbo</option>
                            <option value="30"  >🔥 DeepSeek R1</option>
                            <option value="31"  >DeepSeek V3</option>
                            <option value="33"  >🏆 Gemini 2.5 Pro NEW</option>
                            <option value="34"  >♾️ Gemini 2.5 Flash</option>
                            <option value="35"  >🏆 Qwen 3</option>
                            <option value="36"  >Qwen 3 Thinking</option>
                            <option value="39"  >🏆 Claude 4 Sonnet</option>
                            <option value="40"  >Claude 3.7 Sonnet</option>
                            <option value="41"  >Claude 3.7 Sonnet Extended</option>
                            <option value="42"  >Claude 3.5 Sonnet New</option>
                            <option value="44"  >♾️ Claude 3.5 Haiku</option>
                            <option value="46"  >Claude 3 Opus</option>
                            <option value="60"  >🏆 Grok 3</option>
                            <option value="61"  >Grok 3 Reasoner</option>
                            <option value="62"  >Grok 3 Deep Search</option>
                            <option value="63"  >Grok 3 Images Generation (BETA)</option>
                            <option value="98"  >🔥 Perplexity Sonar Pro + Internet</option>
                            <option value="99"  >Perplexity Sonar Deep Research</option>
                            <option value="100"  >♾️ Perplexity Sonar + Internet</option>
                        </select>
                        <i class="bi bi-chevron-down select-arrow"></i>
                    </div>
                </div>
            </div>
            <div class="square" id="features">
                <!-- Features block content -->
                <div id="feature-o1-mini" class="feature-content" style="display:none;">
                    <p>Компактная и быстрая версия, которая сохраняет мощные логические возможности, но при этом ориентирована на STEM-дисциплины (математика, технологии, инженерия, программирование). Если вам требуется скорость и высокая производительность для решения технических задач без необходимости в обширных мировых знаниях, o1-mini станет вашим лучшим помощником. Работает значительно быстрее благодаря своей оптимизированной структуре.</p>

                    <b>Дополнительные возможности:</b> <br />
                    <i class="bi bi-check2 ok"></i> Голосовое управление <br />
                    <i class="bi bi-x-lg err"></i> Выход в интернет <br />
                    <i class="bi bi-x-lg err"></i> Генерация изображений <br />
                    <i class="bi bi-x-lg err"></i> Обработка изображений <br />
                    <i class="bi bi-x-lg err"></i> Обработка файлов <br />
                    <i class="bi bi-check2 ok"></i> Преднастройки <br />
                    <i class="bi bi-x-lg err"></i> Выдача файлов
                </div>
                <!-- Добавьте остальные модели здесь -->
            </div>
            <p><i class="bi bi-bug-fill danger"></i> - в режиме beta тестирования (может не работать).</p>
        </form>
        <button type="button" @click="saveAndGo">Закрыть окно</button>
    </section>
</template>

<style scoped>

</style>