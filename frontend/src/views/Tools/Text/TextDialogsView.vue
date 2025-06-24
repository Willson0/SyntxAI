<script>
import TextModelsNavigationComponents from "@/components/TextModelsNavigationComponents.vue";
import axios from "axios";
import config from "@/config.json";

export default {
    name: "TextDialogsView",
    components: {TextModelsNavigationComponents},
    data () {
        return {
            dialogs: [],
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
    async mounted () {
        await axios.post(config.backend + "dialog/my", {
            initData: window.Telegram.WebApp.initData,
        }).then((response) => {
            this.dialogs = response.data;
        })

        let currentlyEditing = null;

        document.body.addEventListener('click', function(event) {
            if (event.target.classList.contains('edit-icon')) {
                startEdit(event.target);
            } else if (event.target.classList.contains('save-icon')) {
                saveEdit(event.target);
            } else if (event.target.classList.contains('cancel-icon')) {
                cancelEdit(event.target);
            }
        });

        // Обработчик события input на поле поиска
        const searchField = document.getElementById('search_field');
        searchField.addEventListener('input', function() {
            const searchValue = searchField.value.toLowerCase();
            const conversationItems = document.querySelectorAll('.conversation-item');

            conversationItems.forEach(function(item) {
                const titleText = item.querySelector('.title-text').textContent.toLowerCase();
                if (titleText.includes(searchValue)) {
                    item.closest('section').style.display = '';
                } else {
                    item.closest('section').style.display = 'none';
                }
            });
        });

        function closeAllEdits() {
            if (currentlyEditing) {
                const titleContainer = currentlyEditing.closest('.title');
                const titleText = titleContainer.querySelector('.title-text');
                const titleEdit = titleContainer.querySelector('.title-edit');
                const editIcon = titleContainer.querySelector('.edit-icon');
                const saveIcon = titleContainer.querySelector('.save-icon');
                const cancelIcon = titleContainer.querySelector('.cancel-icon');

                titleText.style.display = 'inline';
                titleEdit.style.display = 'none';
                editIcon.style.display = 'inline-block';
                saveIcon.style.display = 'none';
                cancelIcon.style.display = 'none';

                currentlyEditing = null;
            }
        }

        function startEdit(element) {
            closeAllEdits();

            const titleContainer = element.closest('.title');
            const titleText = titleContainer.querySelector('.title-text');
            const titleEdit = titleContainer.querySelector('.title-edit');
            const editIcon = titleContainer.querySelector('.edit-icon');
            const saveIcon = titleContainer.querySelector('.save-icon');
            const cancelIcon = titleContainer.querySelector('.cancel-icon');

            titleText.style.display = 'none';
            titleEdit.style.display = 'inline-block';
            titleEdit.value = titleText.textContent;
            editIcon.style.display = 'none';
            saveIcon.style.display = 'inline-block';
            cancelIcon.style.display = 'inline-block';

            titleEdit.focus();
            currentlyEditing = element;

            // Добавляем обработчик события input
            titleEdit.addEventListener('input', function(e) {
                validateInput(e.target, saveIcon);
            });

            // Изначально проверяем валидность ввода
            validateInput(titleEdit, saveIcon);
        }

        function validateInput(input, saveButton) {
            const regex = /^[a-zA-Z0-9а-яА-Я\s.!?,\"«»]+$/;
            if (!regex.test(input.value) || input.value.trim() === '') {
                input.style.borderColor = 'red';
                saveButton.classList.add('disabled');
                saveButton.style.pointerEvents = 'none';
            } else {
                input.style.borderColor = '';
                saveButton.classList.remove('disabled');
                saveButton.style.pointerEvents = 'auto';
            }
        }

        function saveEdit(element) {
            if (element.classList.contains('disabled')) {
                return; // Если кнопка неактивна, прерываем выполнение функции
            }

            const titleContainer = element.closest('.title');
            const titleText = titleContainer.querySelector('.title-text');
            const titleEdit = titleContainer.querySelector('.title-edit');
            const editIcon = titleContainer.querySelector('.edit-icon');
            const saveIcon = titleContainer.querySelector('.save-icon');
            const cancelIcon = titleContainer.querySelector('.cancel-icon');

            const newTitle = titleEdit.value.trim();

            if (newTitle) {
                titleText.textContent = newTitle;
                const conversationId = titleContainer.closest('.conversation-item').dataset.conversationId;
                saveToServer(conversationId, newTitle);
            }

            titleText.style.display = 'inline';
            titleEdit.style.display = 'none';
            editIcon.style.display = 'inline-block';
            saveIcon.style.display = 'none';
            cancelIcon.style.display = 'none';
            titleEdit.style.borderColor = ''; // Сбрасываем цвет рамки

            currentlyEditing = null;
        }

        function cancelEdit(element) {
            const titleContainer = element.closest('.title');
            const titleText = titleContainer.querySelector('.title-text');
            const titleEdit = titleContainer.querySelector('.title-edit');
            const editIcon = titleContainer.querySelector('.edit-icon');
            const saveIcon = titleContainer.querySelector('.save-icon');
            const cancelIcon = titleContainer.querySelector('.cancel-icon');

            titleText.style.display = 'inline';
            titleEdit.style.display = 'none';
            editIcon.style.display = 'inline-block';
            saveIcon.style.display = 'none';
            cancelIcon.style.display = 'none';

            currentlyEditing = null;
        }

        function saveToServer(conversationId, newTitle) {
            const userId = '1337592809'; // Получаем ID пользователя из PHP
            const hash = '84a453feae59cdc920c2a2dd68b5765b'; // Получаем хеш из PHP

            fetch('update_title.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: 'id=' + encodeURIComponent(conversationId) +
                    '&title=' + encodeURIComponent(newTitle) +
                    '&user_id=' + encodeURIComponent(userId) +
                    '&hash=' + encodeURIComponent(hash)
            })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        console.log('Title updated successfully');
                    } else {
                        console.error('Failed to update title:', data.message);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                });
        }

        // Принудительное открытие ссылок в том же окне
        document.querySelectorAll('a').forEach(function(link) {
            link.addEventListener('click', function(event) {
                event.preventDefault();
                window.location.href = link.href;
            });
        });
    },
    methods: {
        async confirmDelete(id) {
            if (confirm('Вы уверены, что хотите удалить эту запись?')) {
                await axios.post (config.backend + "dialog/" + id + "/delete", {
                    initData: window.Telegram.WebApp.initData,
                }).then((response) => {
                    this.dialogs = response.data;

                    const messageBox = document.getElementById('error_message');
                    messageBox.style.display = 'block';
                    setTimeout(() => {
                        messageBox.style.display = 'none';
                    }, 3000);
                })
            }
        }
    }
}
</script>

<template>
    <div id="error_message"><i class="bi bi-clipboard2-check-fill"></i> Диалог удалён.</div>
    <div class="notification">
        <i class="bi bi-info-square-fill"></i> Устанавливайте различные модели, используйте GPTs, сохраняйте свои индивидуальные настройки и контролируйте запросы в этом меню.</div>

    <TextModelsNavigationComponents />

    <section class="margin" style="padding: 3px 10px;">
        <div class="form-group">
            <div class="">
                <div class="input-group">
                    <span class="input-group-addon"><i class="bi bi-search"></i></span>
                    <input type="text" name="search" class="input-field no-left-border" placeholder="Поиск диалога" id="search_field"/>
                </div>
            </div>
        </div>
    </section>

    <section class="box margin">
        <div class="conversation-item" v-for="(dialog, key) in dialogs">
<!--            <div class="status">-->
<!--                <i class="bi bi-square-fill ok"></i>-->
<!--            </div>-->
            <div class="content">
                <div style="overflow:hidden;" class="title">
                    <span class="title-text" style="white-space: nowrap; text-overflow: ellipsis">Тема: {{ dialog[0].content }}</span>
<!--                    <input type="text" class="title-edit" maxlength="250" style="display:none;">-->
<!--                    <i class="bi bi-pencil edit-icon"></i>-->
<!--                    <i class="bi bi-check-lg save-icon" style="display:none;"></i>-->
<!--                    <i class="bi bi-x-lg cancel-icon" style="display:none;"></i>-->
                </div>
<!--                <span class="date">&gt; 18.06 22:56</span>-->
            </div>
            <div class="button-container">
                <form method="POST" style="display:inline;">
                    <input type="hidden" name="id" value="d06d0694-4823-c493-c2c83e98">
                    <button type="button" name="delete" class="err" @click="confirmDelete(key)">
                        <i class="bi bi-x-lg"></i> Удалить                </button>
                </form>
                <a target="_self" :href="'/text/dialogs/' + key">
                    <i class="bi bi-box-arrow-up-right"></i> Посмотреть            </a>
            </div>
        </div>
    </section>
</template>

<style  scoped>
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
</style>