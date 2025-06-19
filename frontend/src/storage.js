import { createStore } from 'vuex';

const store = createStore({
    state: {
        user: {},
        interval: null,
    },
    mutations: {
        setUser(state, newValue) {
            state.user = newValue;
        },
        setInterval(state, newValue) {
            clearInterval(state.interval);
            state.interval = newValue;
        },
    },
    actions: {
        updateUser ({ commit }, newValue) {
            commit('setUser', newValue);
        },
        updateInterval ({ commit }, newValue) {
            commit('setInterval', newValue);
        },
    },
});

export default store;
