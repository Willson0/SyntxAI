<script>
import TextModelsNavigationComponents from "@/components/TextModelsNavigationComponents.vue";

export default {
    name: "TextSettingsView",
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
        const summaryInput = document.getElementById('summary');
        const errorSymbols = document.getElementById('error_sybols');
        const errorStrlen = document.getElementById('error_strlen');
        const charCount = document.getElementById('char_count');

        // Добавляем определение регулярного выражения для допустимых символов
        const allowedChars = /^[\p{L}\p{N}\s\p{P}]+$/u;

        const summaryStatus = 1;

        // Устанавливаем прозрачность поля summary на основе summary_status
        if (summaryStatus === 0) {
            summaryInput.style.opacity = '0.5';
        } else {
            summaryInput.style.opacity = '1';
        }

        function showError(element) {
            element.style.display = 'block';
            setTimeout(() => {
                hideError(element);
            }, 3000);
        }

        function hideError(element) {
            element.style.display = 'none';
        }

        function updateCharCount() {
            if (summaryInput && charCount) {
                charCount.textContent = `${summaryInput.value.length}/3000`;
            }
        }

        function validateInput() {
            if (!summaryInput) return;

            const value = summaryInput.value;

            if (value.length > 3000) {
                showError(errorStrlen);
                summaryInput.value = value.slice(0, 3000);
            } else {
                hideError(errorStrlen);
            }

            updateCharCount();
        }

        if (summaryInput) {
            summaryInput.addEventListener('input', function (event) {
                const value = summaryInput.value;
                if (value.length > 0) {
                    const lastChar = value[value.length - 1];

                    // Validate only the last character
                    if (lastChar && !allowedChars.test(lastChar)) {
                        summaryInput.value = value.slice(0, -1);
                        showError(errorSymbols);
                    } else {
                        hideError(errorSymbols);
                    }
                }

                validateInput();
            });

            summaryInput.addEventListener('blur', validateInput);

            summaryInput.addEventListener('paste', function (event) {
                setTimeout(validateInput, 100);
            });

            summaryInput.addEventListener('cut', function (event) {
                setTimeout(validateInput, 100);
            });

            // Initialize character count on page load
            updateCharCount();
        }
    },
    methods: {
        saveAndGo() {
            window.Telegram.WebApp.close();
        },
        updateShowModel(checkbox) {
            var form = document.createElement('form');
            form.method = 'POST';
            form.action = ''; // текущий URL

            var input = document.createElement('input');
            input.type = 'hidden';
            input.name = 'showModel';
            input.value = checkbox.checked ? '1' : '0';

            form.appendChild(input);

            var formIdInput = document.createElement('input');
            formIdInput.type = 'hidden';
            formIdInput.name = 'form_id';
            formIdInput.value = 'settings_form';

            form.appendChild(formIdInput);

            document.body.appendChild(form);
            form.submit();
        },
        updateSummaryStatus(checkbox) {
            var form = document.createElement('form');
            form.method = 'POST';
            form.action = ''; // текущий URL

            var input = document.createElement('input');
            input.type = 'hidden';
            input.name = 'summary_status';
            input.value = checkbox.checked ? '1' : '0';

            form.appendChild(input);

            var formIdInput = document.createElement('input');
            formIdInput.type = 'hidden';
            formIdInput.name = 'form_id';
            formIdInput.value = 'settings_form';

            form.appendChild(formIdInput);

            document.body.appendChild(form);
            form.submit();
        },
    }
}
</script>

<template>
    <div class="notification">
        <i class="bi bi-info-square-fill"></i> Устанавливайте различные модели, используйте GPTs, сохраняйте свои индивидуальные настройки и контролируйте запросы в этом меню.</div>

    <TextModelsNavigationComponents />

    <form class="settings-form" method="post" action="">

        <section class="box margin" style="padding: 3px 10px;">
            <input type="hidden" name="form_id" value="settings_form">

            <!-- Селект для аудио ответов -->
            <div class="form-group">
                <div class="label-and-input">
                    <label for="audioAnswers">
                        Аудио ответы:
                    </label>
                    <div class="select-wrapper">
                        <select name="audioAnswers" class="select-input" onchange="this.form.submit()">
                            <option value="0" >Выключить</option>
                            <option value="1" >На любой запрос</option>
                            <option value="2" >Только на текст</option>
                            <option value="3" selected>Только на голос</option>
                        </select>
                        <i class="bi bi-chevron-down select-arrow"></i>
                    </div>
                </div>
            </div>

            <!-- Новый селект для выбора голоса -->
            <div class="form-group">
                <div class="label-and-input">
                    <label for="voiceChoice">
                        Выбор голоса:
                    </label>
                    <div class="select-wrapper">
                        <select name="voiceChoice" class="select-input" onchange="this.form.submit()">
                            <option value="syntx" selected>Syntx (F)</option>
                            <option value="shimmer" >Shimmer (F)</option>
                            <option value="echo" >Echo (M)</option>
                            <option value="onyx" >Onyx (M)</option>
                            <option value="fable" >Fable (M)</option>
                            <option value="alloy" >Alloy (N)</option>
                        </select>
                        <i class="bi bi-chevron-down select-arrow"></i>
                    </div>
                </div>
            </div>
        </section>

        <!-- Переключатель для showModel -->
        <section class="margin" style="display: flex; justify-content: space-between; align-items: center; margin: 10px 20px;">
            <span class="switch-label">Выводить название модели:</span>
            <label class="switch">
                <input type="checkbox" id="showModel" name="showModel" value="1"
                       checked   onchange="updateShowModel(this)">
                <span class="slider"></span>
            </label>
        </section>

        <div class="notification">
            <i class="bi bi-info-square-fill"></i> Переключатель позволяет отключать вывод названия языковой модели в ответе, например для удобного копирования текста.</div>

        <!-- Переключатель для summary_status -->
        <section class="margin" style="display: flex; justify-content: space-between; align-items: center; margin: 20px 20px 0px 20px;">
            <span class="switch-label">Индивидуальные указания:</span>
            <label class="switch">
                <input type="checkbox" id="summary_status" name="summary_status" value="1"
                       checked   onchange="updateSummaryStatus(this)">
                <span class="slider"></span>
            </label>
        </section>

    </form>

    <section class="margin" style="margin-bottom: 300px; margin-top: 0;">

        <div id="error_sybols" style="display: none; position: fixed; top: 20px; left: 50%; transform: translateX(-50%); width: 90%; background-color: #cf2d2d; color: white; padding: 10px; border-radius: 5px; z-index: 1000;">
            <i class="bi bi-exclamation-square-fill"></i> Ошибка: Введены недопустимые символы.
        </div>

        <div id="error_strlen" style="display: none; position: fixed; top: 20px; left: 50%; transform: translateX(-50%); width: 90%; background-color: #cf2d2d; color: white; padding: 10px; border-radius: 5px; z-index: 1000;">
            <i class="bi bi-exclamation-square-fill"></i> Превышено максимальное количество символов.
        </div>
        <form class="settings-form" method="post" action="">
            <input type="hidden" name="form_id" value="summary_form">
            <div class="form-group" style="position: relative;">
                <textarea id="summary" name="summary" maxlength="3000" rows="5" placeholder="Пользовательские инструкции позволяют указать все, что вы хотели бы, чтобы ChatGPT учел в своем ответе. Введите пожелания и ваши установки будут добавляться в новые разговоры в дальнейшем." style=""></textarea>
                <div id="char_count" style="position: absolute; right: 10px; bottom: 10px; color: grey;">0/3000</div>
            </div>
            <button type="submit">Сохранить</button>
            <button type="button" @click="saveAndGo">Закрыть окно</button>
        </form>
    </section>
</template>

<style scoped>
    section {
        padding: 0;
    }
</style>