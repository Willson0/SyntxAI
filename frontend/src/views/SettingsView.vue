<script>
import HeaderComponent from "@/components/HeaderComponent.vue";
import NavigationComponent from "@/components/NavigationComponent.vue";
import config from "@/config.json";
import axios from "axios";

export default {
    name: "SettingsView",
    components: {NavigationComponent, HeaderComponent},
    data () {
        return {
        }
    },
    mounted () {
        const form = document.getElementById('settingsForm');
        const checkboxes = form.querySelectorAll('input[type="checkbox"]');

        checkboxes.forEach(checkbox => {
            checkbox.addEventListener('change', function() {
                const field = this.name;
                const value = this.checked ? '1' : '0';

                fetch('', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: `action=update_settings&field=${field}&value=${value}`
                })
                    .then(response => response.json())
                    .then(data => {
                        if (!data.success) {
                            console.error('Error updating setting:', data.message);
                            // Можно добавить визуальное уведомление пользователя об ошибке
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        // Можно добавить визуальное уведомление пользователя об ошибке
                    });
            });
        });
    },
    methods: {
        async sendData() {
            await axios.post(config.backend + "auth/settings", {
                initData: window.Telegram.WebApp.initData,
                notify_tokens: this.user.notify_tokens,
                notify_refs: this.user.notify_refs,
                notify_refs_buys: this.user.notify_refs_buys,
                notify_about_updates: this.user.notify_about_updates,
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
   <HeaderComponent />

    <form id="settingsForm"><input type="hidden" name="PHPSESSID" value="ec48f339284f0424b862e2132a3c98e7" />
        <input type="hidden" name="form_id" value="settings_form">
        <section class="margin" style="display: flex; justify-content: space-between; align-items: center;">
            <span class="switch-label">Получать уведомления об обновлениях</span>
            <label class="switch">
                <input type="hidden" name="notifications" value="0">
                {{user.notify_about_updates}}
                <input @input="user.notify_about_updates = !Boolean(user.notify_about_updates); sendData()" :checked="user.notify_about_updates" type="checkbox" id="notifications" name="notifications">
                <span class="slider"></span>
            </label>
        </section>
        <section class="box white margin">
            <i class="bi bi-info-square-fill aqua"></i> <b>Важно:</b> В чат-боте нет сторонней рекламы — только спокойствие и комфорт для вас. Но мы регулярно обновляем сервисы, внедряем инструменты и проводим эксклюзивные мастер-классы. Отказываясь от рассылки, вы рискуете пропустить информацию о важных обновлениях и мероприятиях.    </section>
        <section class="margin" style="display: flex; justify-content: space-between; align-items: center;">
            <span class="switch-label">Уведомлять о списании токенов</span>
            <label class="switch">
                <input type="hidden" name="tokensInfo" value="0">
                <input @input="user.notify_tokens = !Boolean(user.notify_tokens); sendData()" :checked="user.notify_tokens" type="checkbox" id="tokensInfo" name="tokensInfo" value="1" checked>
                <span class="slider"></span>
            </label>
        </section>
        <section class="box white margin">
            <i class="bi bi-info-square-fill aqua"></i> Настройка уведомлений о списании токенов после каждой генерации нейросетей в чат-боте.    </section>
        <section class="margin" style="display: flex; justify-content: space-between; align-items: center;">
            <span class="switch-label">Новые рефералы</span>
            <label class="switch">
                <input type="hidden" name="notifications_newAffs" value="0">
                <input @input="user.notify_refs = !Boolean(user.notify_refs); sendData()" :checked="user.notify_refs" type="checkbox" id="notifications_newAffs" name="notifications_newAffs" value="1" checked>
                <span class="slider"></span>
            </label>
        </section>
        <section class="box white margin">
            <i class="bi bi-info-square-fill aqua"></i> Настройка уведомлений о регистрации новых рефералов по партнерской программе.    </section>
        <section class="margin" style="display: flex; justify-content: space-between; align-items: center;">
            <span class="switch-label">Оплаты рефералами</span>
            <label class="switch">
                <input type="hidden" name="notifications_AffsPays" value="0">
                <input @input="user.notify_refs_buys = !Boolean(user.notify_refs_buys); sendData()" :checked="user.notify_refs_buys" type="checkbox" id="notifications_AffsPays" name="notifications_AffsPays" value="1" checked>
                <span class="slider"></span>
            </label>
        </section>
        <section class="box white margin">
            <i class="bi bi-info-square-fill"></i> Настройка уведомлений о покупках и зачисляемых бонусов рефералами.    </section>
    </form>

    <div class="bottom"></div>
    <NavigationComponent />
</template>

<style scoped>

</style>