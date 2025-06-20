<script>
import config from "@/config.json"
export default {
    name: "TextChatView",
    data () {
        return {
            config: config,
        }
    },
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
        document.body.style.fontFamily = "ui-sans-serif, -apple-system, system-ui, Segoe UI, Roboto, Ubuntu, Cantarell, Noto Sans, sans-serif, Helvetica, Apple Color Emoji, Arial, Segoe UI Emoji, Segoe UI Symbol"
        document.body.style.minHeight = "unset";

        // Обработчик для кнопки "Скопировать всё"
        document.getElementById('copyAllButton').addEventListener('click', () => {
            const messages = document.querySelectorAll('.message');
            let fullDialog = '';

            messages.forEach((message) => {
                const content = message.querySelector('.content');
                const isUserMessage = message.classList.contains('userquestion');
                let messageContent = this.cleanContent(content, isUserMessage);

                if (isUserMessage) {
                    fullDialog += 'User: ' + messageContent + '\n\n';
                } else {
                    fullDialog += 'Assistant: ' + messageContent + '\n\n';
                }
            });

            // Копируем текст
            if (this.copyTextToClipboard(fullDialog.trim())) {
                const button = document.getElementById('copyAllButton');
                button.innerHTML = '<i class="bi bi-check2"></i> Скопировано!';
                setTimeout(() => {
                    button.innerHTML = '<i class="bi bi-clipboard"></i> ';
                }, 2000);
            }
        });


        document.querySelectorAll('.copy-button').forEach((button) => {
            button.addEventListener('click', () => {
                const language = button.getAttribute('data-language');
                const codeBlock = button.closest('.code-header').nextElementSibling.querySelector('code.language-' + language);
                const code = codeBlock.innerText;
                navigator.clipboard.writeText(code).then(() => {
                    button.innerHTML = '<i class="bi bi-check2"></i> Скопировано!';
                    setTimeout(() => {
                        button.innerHTML = '<i class="bi bi-copy"></i> Скопировать';
                    }, 2000);
                });
            });
        });

        document.querySelectorAll('.toggle-button').forEach((button) => {
            button.addEventListener('click', () => {
                const codeBlock = button.closest('.code-header').nextElementSibling;
                if (codeBlock.style.display === 'none' || codeBlock.style.display === '') {
                    codeBlock.style.display = 'block';
                    button.innerHTML = '<i class="bi bi-arrows-angle-contract"></i> Закрыть блок';
                } else {
                    codeBlock.style.display = 'none';
                    button.innerHTML = '<i class="bi bi-arrows-angle-expand"></i> Открыть блок';
                }
            });
        });
    },
    methods: {
        copyText(event) {
            event.preventDefault(); // Предотвращает переход по ссылке
            const textToCopy = "https://LLMchat.syntxai.net/d06d0694-4823-c493-c2c83e98";
            navigator.clipboard.writeText(textToCopy).then(() => {
                const messageBox = document.getElementById('error_message');
                messageBox.style.display = 'block';
                setTimeout(() => {
                    messageBox.style.display = 'none';
                }, 3000);
            }).catch(err => {
                console.error("Ошибка копирования текста: ", err);
            });
        },
        copyMessage (button, type) {
            const contentElement = button.closest('.content');
            let messageContent = this.cleanContent(contentElement, type === 'user');

            if (this.copyTextToClipboard(messageContent)) {
                const originalText = button.innerHTML;
                button.innerHTML = '<i class="bi bi-check2"></i> Скопировано!';
                setTimeout(() => {
                    button.innerHTML = originalText;
                }, 2000);
            }
        },
        async copyTextToClipboard(text) {
            let successful = false;

            // Проверяем поддержку современного API буфера обмена
            if (navigator.clipboard && navigator.clipboard.writeText) {
                try {
                    await navigator.clipboard.writeText(text);
                    successful = true;
                } catch (err) {
                    console.error('Failed to copy text: ', err);
                }
            } else {
                // Fallback для устаревших браузеров
                const tempTextArea = document.createElement('textarea');
                tempTextArea.value = text;

                // Делаем элемент невидимым
                tempTextArea.style.position = 'absolute';
                tempTextArea.style.left = '-9999px';
                tempTextArea.style.top = '0';
                tempTextArea.style.opacity = '0';

                document.body.appendChild(tempTextArea);
                tempTextArea.focus();
                tempTextArea.select();

                try {
                    successful = document.execCommand('copy');
                } catch (err) {
                    console.error('Fallback: Oops, unable to copy', err);
                }

                document.body.removeChild(tempTextArea);
            }

            if (successful) {
                const messageBox = document.getElementById('error_message');
                messageBox.style.display = 'block';
                setTimeout(() => {
                    messageBox.style.display = 'none';
                }, 3000);
            }

            return successful;
        },
        cleanContent(element, isUserMessage = false) {
            const clone = element.cloneNode(true);
            clone.querySelectorAll('.code-header, .copy-button, .toggle-button, .copy-message-button').forEach(el => el.remove());
            clone.querySelectorAll('div[style*="opacity: 0.7; font-size: 11px;"]').forEach(el => el.remove());

            let content = clone.innerText || clone.textContent;
            content = content.replace(/\n{3,}/g, '\n\n');
            content = content.split('\n').map(line => line.trim()).join('\n');
            content = content.trim();
            if (isUserMessage) {
                content = content.replace(/\s+/g, ' ').trim();
            }

            return content;
        }
    }
}
</script>

<template>

    <div class="header">
        <a href="/text/dialogs"><i class="bi bi-arrow-return-left"></i> Вернуться</a>
    </div>

    <div id="error_message"><i class="bi bi-clipboard2-check-fill"></i> Ссылка скопирована.</div>

    <section class="container mt-3">
        <div class="markdown-content">
            <div class="message userquestion">
                <div class="content">
                    <p>Привет!</p>                    <button class="copy-message-button" @click="copyMessage($event.target, 'user')">
                    <i class="bi bi-clipboard"></i> Скопировать                    </button>
                </div>
            </div>
            <div class="message answer">
                <div class="logo"><img src="/syntxwhite.png" alt="Logo"></div>
                <div class="content">
                    <p>Привет! Как я могу помочь тебе сегодня?</p>                    <button class="copy-message-button" @click="copyMessage($event.target, 'assistant')">
                    <i class="bi bi-clipboard"></i> Скопировать                    </button>
                    <div style="opacity: 0.7; font-size: 11px;"><i class="bi bi-robot" style="margin-right: 4px;"></i>
                        GPT-o1 Mini                </div>
                    <div style="opacity: 0.7; font-size: 11px;"><i class="bi bi-clock-history" style="margin-right: 4px;"></i>18.06.2025 22:56:58 GMT+3</div>
                </div>
            </div>
        </div>
    </section>

    <!-- Добавьте кнопку "Скопировать весь диалог" -->
    <button id="copyAllButton"><i class="bi bi-clipboard"></i> </button>


    <section class="container footer">
        <img src="/logo.png" alt="" height="15px" width="15px" style="margin: 0 4px">
        <a :href="config.bot">Telegram Bot</a>
    </section>

    <textarea id="temp-textarea" style="position:absolute;left:-9999px;"></textarea>
</template>

<style scoped>
body {
    font-family: ui-sans-serif, -apple-system, system-ui, Segoe UI, Roboto, Ubuntu, Cantarell, Noto Sans, sans-serif, Helvetica, Apple Color Emoji, Arial, Segoe UI Emoji, Segoe UI Symbol;
    padding-top: 35px;
}
h1 {
    font-size: 24px !important;
}

h2 {
    font-size: 22px !important;
}

h3 {
    font-size: 20px !important;
}

h4 {
    font-size: 18px !important;
}

h5 {
    font-size: 16px !important;
}

h6 {
    font-size: 14px !important;
}

pre {
    position: relative;
    white-space: pre-wrap; /* Обрезка длинных строк на малых экранах */
}
.copy-button {
    position: absolute;
    top: 4px;
    right: 118px;
    padding: 3px 5px;
    cursor: pointer;
    color: var(--tg-theme-button-text-color, #ffffff);
    background: none;
    border: none;
    margin: 0;
}
.copy-button:focus {
    outline: none;
}
.toggle-button {
    position: absolute;
    top: 4px;
    right: 10px;
    padding: 3px 5px;
    cursor: pointer;
    color: var(--tg-theme-button-text-color, #ffffff);
    background: none;
    border: none;
    margin: 0;
}
.toggle-button:focus {
    outline: none;
}

table {
    width: 100%;
    border-collapse: collapse;
    font-family: Arial, sans-serif;
    margin-bottom: 1em;
    color: var(--tg-theme-text-color, #222) !important;
}

th, td {
    border: 1px solid var(--tg-theme-secondary-bg-color, #393939) !important;
    padding: 8px;
    text-align: left;
    color: var(--tg-theme-text-color, #999) !important;
}

th {
    background-color: var(--tg-theme-secondary-bg-color, #393939);
}

caption {
    caption-side: bottom;
    padding: 10px;
    font-weight: bold;
}

button {
    width: unset !important;
}

#clipboard_success {
    display: none;
    position: fixed;
    top: 20px;
    left: 50%;
    transform: translateX(-50%);
    width: 90%;
    background-color: green;
    color: white;
    padding: 10px;
    border-radius: 5px;
    z-index: 1000;
    opacity: 0;
    transition: opacity 0.5s;
}
#clipboard_success.show {
    display: block;
    opacity: 1;
}
img {
    max-width: 30%;
    margin: 0 auto;
}

blockquote {
    border-left: 5px solid var(--tg-theme-secondary-bg-color, #393939);
    margin: 25px 10px;
    padding: 10px;
    quotes: "\201C""\201D""\2018""\2019";
    font-size: 20px !important;
}
blockquote:before {
    content: open-quote;
    font-size: 50px;
    line-height: 0.1em;
    margin-right: 0.25em;
    vertical-align: -0.4em;
}
blockquote p {
    display: inline;
}
blockquote blockquote {
    background: var(--tg-theme-secondary-bg-color, #393939);
    border-left: 10px solid #aaa;
    margin: 10px;
    padding: 10px;
}
blockquote blockquote:before {
    color: var(--tg-theme-bg-color, #000);
    font-size: 30px;
}
pre[class*=language-] {
    margin: 0 0 10px 0;
    border-radius: 0 0 10px 10px !important;
    margin-top: -10px;
}
:not(pre)>code[class*=language-], pre[class*=language-] {
    background: #393939;
    border: 1px solid #2f2f2f;
}
.header {
    background: var(--tg-theme-secondary-bg-color, #393939);
    padding: 10px;
    text-align: center;
    position: fixed;
    top: 0;
    width: 100%;
    z-index: 1;
}
.header a {
    text-decoration: none;
    color: var(--tg-theme-text-color, #ccc);
    margin: 0 5px;
    opacity: 0.6;
    font-size: 11px;
}
.header a:hover {
    text-decoration: none;
}
.header .bi {
    margin-right: 5px;
}
#error_message {
    display: none;
    position: fixed;
    top: 20px;
    left: 50%;
    transform: translateX(-50%);
    background-color: green;
    color: white;
    padding: 10px;
    border-radius: 5px;
    z-index: 1000;
    white-space: nowrap; /* Prevents text from wrapping */
}
.footer {
    padding: 10px;
    margin-top: 10px;
    border-top: 1px solid var(--tg-theme-secondary-bg-color, #393939);
    font-size: 11px;
}
mjx-container[jax="CHTML"][display="true"] {
    font-size: 16px !important; /* Задайте нужный размер шрифта */
    font-family: ui-sans-serif, -apple-system, system-ui, Segoe UI, Roboto, Ubuntu, Cantarell, Noto Sans, sans-serif, Helvetica, Apple Color Emoji, Arial, Segoe UI Emoji, Segoe UI Symbol;
    background: var(--tg-theme-secondary-bg-color, #393939);
    padding: 10px;
    border-radius: 8px;
    display: inline-block;
    text-align: left !important;
}
mjx-container[jax="CHTML"][display="true"] mjx-math {
}
.message {
    display: flex;
    margin: 10px 0;
}
.message.userquestion {
    justify-content: flex-end;
}
.message.answer {
    justify-content: flex-start;
}
.message .content {
    max-width: 90%;
    padding: 0 10px;
    border-radius: 8px;
}
.message.userquestion .content {
    text-align: right;
    max-width: 75%;
    background: var(--tg-theme-secondary-bg-color, #393939);
    padding-top: 0;
}
.message.answer .content {
    text-align: left;
}

.message.answer .logo {
    width: 25px; /* Задаем фиксированную ширину 100px */
    margin-right: 5px; /* Отступ справа от логотипа */
    flex-shrink: 0; /* Предотвращаем сжатие контейнера логотипа */
    display: flex;
    align-items: flex-start; /* Выравниваем логотип по верхнему краю */
    justify-content: center;
}

.message.answer .logo img {
    max-width: 100%; /* Максимальная ширина изображения 100% */
    max-height: 100%; /* Максимальная высота изображения 100% */
    border-radius: 50%; /* Делаем изображение кругом */
    object-fit: cover; /* Обеспечиваем, что изображение полностью занимает контейнер, сохраняя пропорции */
}

.userquestion p {
    margin-bottom: 0;
}

.file_user {
    padding: 10px 0;
    display: flex;
    align-items: center;
}
.file_user i {
    padding-right: 10px;
    font-size: 25px;
}

dl, ol, ul {
    padding-left: 20px;
}

#copyAllButton {
    position: fixed;
    bottom: 20px;
    right: 20px;
    z-index: 1000;
    padding: 5px 8px;
    background-color: var(--tg-theme-bg-color, #50a8eb);
    border: 1px solid var(--tg-theme-secondary-bg-color, #ffffff);
    color: var(--tg-theme-text-color, #ccc);
    border-radius: 5px;
    cursor: pointer;
}

.copy-message-button {
    padding: 5px 10px;
    background-color: var(--tg-theme-secondary-bg-color, #393939);
    color: var(--tg-theme-text-color, #ffffff);
    border: none;
    border-radius: 3px;
    cursor: pointer;
    font-size: 12px;
    margin-top: 5px;
}

#temp-textarea {
    position: fixed;
    top: 0;
    left: 0;
    opacity: 0;
    pointer-events: none;
}

.container {
    margin: 10px 0;
    width: 100%;
    box-sizing: border-box;
    margin-top: 35px;
}
.container.footer {
    margin-top: 0;
    -webkit-border-radius: 0;
    -moz-border-radius: 0;
    border-radius: 0 !important;
}
.markdown-content {
    width: 100%;
}
</style>