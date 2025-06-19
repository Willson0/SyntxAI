<script>
export default {
    name: "TextModelsNavigationComponents",
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
}
</script>

<template>
    <nav class="top-nav">
        <ul>
            <li><a href="/text/models" class="active"><i class="bi bi-robot"></i>Все модели</a></li>
            <li><a href="/text/settings" ><i class="bi bi-gear-fill"></i>Настройки</a></li>
            <li><a href="/text/dialogs" ><i class="bi bi-chat-right-text-fill"></i>Диалоги</a></li>
        </ul>
    </nav>
</template>

<style scoped>

</style>