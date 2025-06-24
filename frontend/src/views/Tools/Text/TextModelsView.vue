<script>
import TextModelsNavigationComponents from "@/components/TextModelsNavigationComponents.vue";
import config from "@/config.json";
import axios from "axios";

export default {
    name: "TextModelsView",
    components: {TextModelsNavigationComponents},
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

    },
    methods: {
        saveAndGo () {
            window.Telegram.WebApp.close();
        },
        async sendData() {
            await axios.post(config.backend + "auth/settings", {
                initData: window.Telegram.WebApp.initData,
                model: this.user.model,
            }).then((response) => {
                this.$store.dispatch("updateUser", response.data);
            }).catch((error) => {
                alert("Ошибка:\n" + error.response.data.message);
            });
        }
    },
    computed: {
        user() {
            return this.$store.state.user;
        },
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
                    <span class="stat-number">{{ user.sub_name }}</span>
                </div>
            </div>
<!--            <div class="stat-item">-->
<!--                <div>-->
<!--                    <span class="stat-icon"><i class="bi bi-person-workspace"></i></span>-->
<!--                    <span class="stat-text">Доступно пробных запроса</span>-->
<!--                    <span class="stat-number">5 ✏️</span>-->
<!--                </div>-->
<!--            </div>-->
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
                        <select id="model" name="model" class="select-input" v-model="user.model" @change="sendData">
                            <option :value="key" v-for="(value, key) in config.models">{{ value }}</option>
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

<style  scoped>

</style>