<script setup>
import AppLayout from "@/Layouts/AppLayout.vue";
import DeleteUserForm from "@/Pages/Profile/Partials/DeleteUserForm.vue";
import LogoutOtherBrowserSessionsForm from "@/Pages/Profile/Partials/LogoutOtherBrowserSessionsForm.vue";
import SectionBorder from "@/Components/SectionBorder.vue";
import TwoFactorAuthenticationForm from "@/Pages/Profile/Partials/TwoFactorAuthenticationForm.vue";
import UpdatePasswordForm from "@/Pages/Profile/Partials/UpdatePasswordForm.vue";
import UpdateProfileInformationForm from "@/Pages/Profile/Partials/UpdateProfileInformationForm.vue";

defineProps({
    confirmsTwoFactorAuthentication: Boolean,
    sessions: Array,
    leave_credits: {
        type: Array,
        default: () => [],
    },
});
</script>

<template>
    <AppLayout title="Profile">
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Profile
            </h2>
        </template>

        <div>
            <div class="max-w-7xl mx-auto py-10 sm:px-6 lg:px-8">
                <div v-if="$page.props.jetstream.canUpdateProfileInformation">
                    <UpdateProfileInformationForm
                        :user="$page.props.auth.user"
                    />

                    <SectionBorder />
                </div>

                <!-- Leave Credits Section -->
                <div v-if="leave_credits && leave_credits.length">
                    <section>
                        <header>
                            <h2 class="text-lg font-medium text-gray-900">
                                Leave Credits
                            </h2>
                            <p class="mt-1 text-sm text-gray-600">
                                Your available leave balance for
                                {{ new Date().getFullYear() }}.
                            </p>
                        </header>

                        <div
                            class="mt-6 grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3"
                        >
                            <div
                                v-for="credit in leave_credits"
                                :key="credit.id"
                                class="relative rounded-2xl border bg-white p-5 shadow-sm"
                                :class="
                                    credit.remaining_days > 0
                                        ? 'border-gray-200'
                                        : 'border-red-200 bg-red-50/40'
                                "
                            >
                                <div class="flex items-start justify-between">
                                    <div>
                                        <span
                                            class="inline-flex items-center rounded-md px-2 py-0.5 text-xs font-semibold ring-1 ring-inset"
                                            :class="
                                                credit.is_paid
                                                    ? 'bg-green-50 text-green-700 ring-green-600/20'
                                                    : 'bg-orange-50 text-orange-700 ring-orange-600/20'
                                            "
                                        >
                                            {{ credit.leave_type_code }}
                                        </span>
                                        <p
                                            class="mt-2 text-sm font-medium text-gray-800"
                                        >
                                            {{ credit.leave_type }}
                                        </p>
                                    </div>
                                    <span
                                        class="text-xs font-medium px-2 py-1 rounded-full"
                                        :class="
                                            credit.remaining_days > 0
                                                ? 'bg-green-100 text-green-700'
                                                : 'bg-red-100 text-red-600'
                                        "
                                    >
                                        {{ credit.remaining_days }} left
                                    </span>
                                </div>

                                <!-- Progress bar -->
                                <div class="mt-4">
                                    <div
                                        class="flex justify-between text-xs text-gray-500 mb-1"
                                    >
                                        <span
                                            >Used:
                                            {{ credit.used_days }} day(s)</span
                                        >
                                        <span
                                            >Total:
                                            {{ credit.total_days }} day(s)</span
                                        >
                                    </div>
                                    <div
                                        class="h-2 w-full rounded-full bg-gray-200 overflow-hidden"
                                    >
                                        <div
                                            class="h-2 rounded-full transition-all"
                                            :class="
                                                credit.remaining_days > 0
                                                    ? 'bg-green-500'
                                                    : 'bg-red-500'
                                            "
                                            :style="`width: ${credit.total_days > 0 ? Math.min(100, (credit.used_days / credit.total_days) * 100) : 0}%`"
                                        />
                                    </div>
                                </div>
                            </div>
                        </div>
                    </section>

                    <SectionBorder />
                </div>

                <div v-if="$page.props.jetstream.canUpdatePassword">
                    <UpdatePasswordForm class="mt-10 sm:mt-0" />

                    <SectionBorder />
                </div>

                <div
                    v-if="
                        $page.props.jetstream.canManageTwoFactorAuthentication
                    "
                >
                    <TwoFactorAuthenticationForm
                        :requires-confirmation="confirmsTwoFactorAuthentication"
                        class="mt-10 sm:mt-0"
                    />

                    <SectionBorder />
                </div>

                <LogoutOtherBrowserSessionsForm
                    :sessions="sessions"
                    class="mt-10 sm:mt-0"
                />

                <template
                    v-if="$page.props.jetstream.hasAccountDeletionFeatures"
                >
                    <SectionBorder />

                    <DeleteUserForm class="mt-10 sm:mt-0" />
                </template>
            </div>
        </div>
    </AppLayout>
</template>
