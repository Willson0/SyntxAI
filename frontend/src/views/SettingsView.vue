<script>
import HeaderComponent from "@/components/HeaderComponent.vue";
import NavigationComponent from "@/components/NavigationComponent.vue";

export default {
    name: "SettingsView",
    components: {NavigationComponent, HeaderComponent},
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
                <input type="checkbox" id="notifications" name="notifications" value="1" checked>
                <span class="slider"></span>
            </label>
        </section>
        <section class="box white margin">
            <i class="bi bi-info-square-fill aqua"></i> <b>Важно:</b> В чат-боте нет сторонней рекламы — только спокойствие и комфорт для вас. Но мы регулярно обновляем сервисы, внедряем инструменты и проводим эксклюзивные мастер-классы. Отказываясь от рассылки, вы рискуете пропустить информацию о важных обновлениях и мероприятиях.    </section>
        <section class="margin" style="display: flex; justify-content: space-between; align-items: center;">
            <span class="switch-label">Уведомлять о списании токенов</span>
            <label class="switch">
                <input type="hidden" name="tokensInfo" value="0">
                <input type="checkbox" id="tokensInfo" name="tokensInfo" value="1" checked>
                <span class="slider"></span>
            </label>
        </section>
        <section class="box white margin">
            <i class="bi bi-info-square-fill aqua"></i> Настройка уведомлений о списании токенов после каждой генерации нейросетей в чат-боте.    </section>
        <section class="margin" style="display: flex; justify-content: space-between; align-items: center;">
            <span class="switch-label">Новые рефералы</span>
            <label class="switch">
                <input type="hidden" name="notifications_newAffs" value="0">
                <input type="checkbox" id="notifications_newAffs" name="notifications_newAffs" value="1" checked>
                <span class="slider"></span>
            </label>
        </section>
        <section class="box white margin">
            <i class="bi bi-info-square-fill aqua"></i> Настройка уведомлений о регистрации новых рефералов по партнерской программе.    </section>
        <section class="margin" style="display: flex; justify-content: space-between; align-items: center;">
            <span class="switch-label">Оплаты рефералами</span>
            <label class="switch">
                <input type="hidden" name="notifications_AffsPays" value="0">
                <input type="checkbox" id="notifications_AffsPays" name="notifications_AffsPays" value="1" checked>
                <span class="slider"></span>
            </label>
        </section>
        <section class="box white margin">
            <i class="bi bi-info-square-fill"></i> Настройка уведомлений о покупках и зачисляемых бонусов рефералами.    </section>
    </form>

    <div class="bottom"></div>


    <div class="bottom-info">
        Время сервера: 18.06.2025 22:13 | ID: 1337592809    </div>
    <NavigationComponent />
</template>

<style scoped>

</style>