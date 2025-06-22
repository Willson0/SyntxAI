import {createRouter, createWebHistory} from 'vue-router';
import ProfileView from "@/views/ProfileView.vue";
import PartnerView from "@/views/PartnerView.vue";
import MyPartnerView from "@/views/MyPartnerView.vue";
import DocumentationView from "@/views/DocumentationView.vue";
import GiftsView from "@/views/GiftsView.vue";
import SubscriptionsView from "@/views/SubscriptionsView.vue";
import ShopView from "@/views/ShopView.vue";
import SettingsView from "@/views/SettingsView.vue";
import faqView from "@/views/faqView.vue";
import TokensView from "@/views/TokensView.vue";
import TextModelsView from "@/views/Tools/Text/TextModelsView.vue";
import TextSettingsView from "@/views/Tools/Text/TextSettingsView.vue";
import TextDialogsView from "@/views/Tools/Text/TextDialogsView.vue";
import TextChatView from "@/views/Tools/Text/TextChatView.vue";
import ImageSettingsView from "@/views/Tools/Image/ImageSettingsView.vue";
import withdrawView from "@/views/WithdrawView.vue";

const routes = [
    {
        path: "/",
        component: ProfileView,
    },
    {
        path: "/partner",
        component: PartnerView,
    },
    {
        path: "/partner/withdraw",
        component: withdrawView,
    },
    {
        path: "/partner/my",
        component: MyPartnerView,
    },
    {
        path: "/documentation",
        component: DocumentationView,
    },
    {
        path: "/gifts",
        component: GiftsView,
    },
    {
        path: "/subscriptions",
        component: SubscriptionsView,
    },
    {
        path: "/shop",
        component: ShopView,
    },
    {
        path: "/settings",
        component: SettingsView,
    },
    {
        path: "/faq",
        component: faqView,
    },
    {
        path: "/tokens",
        component: TokensView,
    },
    {
        path: "/text/models",
        component: TextModelsView,
    },
    {
        path: "/text/settings",
        component: TextSettingsView,
    },
    {
        path: "/text/dialogs",
        component: TextDialogsView,
    },
    {
        path: "/text/dialogs/:id",
        component: TextChatView,
    },
    {
        path: "/image/settings",
        component: ImageSettingsView,
    }
]

const router = createRouter({
    history: createWebHistory(),
    routes
})

export default router;