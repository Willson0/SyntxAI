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
            <li><a href="/text/models" :class="$route.path === '/text/models' ? 'active' : ''"><i class="bi bi-robot"></i>Все модели</a></li>
            <li><a href="/text/settings" :class="$route.path === '/text/settings' ? 'active' : ''"><i class="bi bi-gear-fill"></i>Настройки</a></li>
            <li><a href="/text/dialogs" :class="$route.path === '/text/dialogs' ? 'active' : ''"><i class="bi bi-chat-right-text-fill"></i>Диалоги</a></li>
        </ul>
    </nav>
</template>

<style scoped>

</style>