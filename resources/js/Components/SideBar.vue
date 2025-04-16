<template>
    <aside id="sidebar" class="sidebar">
        <ul class="sidebar-nav" id="sidebar-nav">
            <li class="nav-item">
                <Link class="nav-link" :href="route('dashboard')" :class="{ collapsed: $page.url !== '/dashboard' }">
                <i class="bi bi-grid"></i>
                <span> {{ $t("dashboard") }} </span>
                </Link>
            </li>


            <li class="nav-item"
                v-if="hasPermission('read admins') || hasPermission('read users') || hasPermission('read specialists') || hasPermission('read companies')">
                <a class="nav-link" data-bs-target="#components-nav-users" data-bs-toggle="collapse" href="#" :class="{
                    collapsed: !isMembersSectionOpen
                }" @click.prevent="toggleMembersSection">
                    <i class="bi bi-lock"></i>
                    <span>{{ $t('Members') }}</span>
                    <i class="bi bi-chevron-down ms-auto"></i>
                </a>
                <ul id="components-nav-users" class="nav-content collapse" :class="{ show: isMembersSectionOpen }">
                    <li v-if="hasPermission('read users')">
                        <Link :href="route('users.index')" :class="{ collapsed: !$page.url.startsWith('/users') }">
                        <i class="bi bi-circle"></i>
                        <span>{{ $t('users') }}</span>
                        </Link>
                    </li>
                    <li v-if="hasPermission('read admins')">
                        <Link :href="route('admins.index')" :class="{ collapsed: !$page.url.startsWith('/admins') }">
                        <i class="bi bi-circle"></i>
                        <span>{{ $t('admins') }}</span>
                        </Link>
                    </li>
                    <li v-if="hasPermission('read companies')">
                        <Link :href="route('companies.index')"
                            :class="{ collapsed: !$page.url.startsWith('/companies') }">
                        <i class="bi bi-circle"></i>
                        <span>{{ $t('companies') }}</span>
                        </Link>
                    </li>
                    <li v-if="hasPermission('read specialists')">
                        <Link :href="route('specialists.index')"
                            :class="{ collapsed: !$page.url.startsWith('/specialists') }">
                        <i class="bi bi-circle"></i>
                        <span>{{ $t('Specialists') }}</span>
                        </Link>
                    </li>
                </ul>
            </li>

            <!-- Roles Section -->
            <li class="nav-item" :hasPermission="['read roles', 'read permissions']"
                v-if="hasPermission('read roles') || hasPermission('read permissions')">
                <a class="nav-link" data-bs-target="#components-nav-roles" data-bs-toggle="collapse" href="#" :class="{
                    collapsed: !isRolesSectionOpen
                }" @click.prevent="toggleRolesSection">
                    <i class="bi bi-lock"></i>
                    <span>{{ $t('roles_control') }}</span>
                    <i class="bi bi-chevron-down ms-auto"></i>
                </a>
                <ul id="components-nav-roles" class="nav-content collapse" :class="{ show: isRolesSectionOpen }">
                    <li>
                        <Link :href="route('roles.index')" :class="{ collapsed: !$page.url.startsWith('/roles') }">
                        <i class="bi bi-circle"></i>
                        <span>{{ $t('roles') }}</span>
                        </Link>
                    </li>
                </ul>
            </li>
            <!-- banners -->
            <!-- <li class="nav-item" v-if="hasPermission('read banners')">
                <Link class="nav-link" :href="route('banners.index')" :class="{
                    collapsed: !$page.url.startsWith('/banners'),
                }">
                <i class="bi bi-image"></i>
                <span>{{ $t("banners") }}</span>
                </Link>
            </li> -->

            <!-- contacts -->
            <li class="nav-item" v-if="hasPermission('read contacts')">
                <Link class="nav-link" :href="route('contacts.index')"
                    :class="{ collapsed: !$page.url.startsWith('/contacts') }">
                <i class="bi bi-inbox"></i>
                <span>{{ $t("contacts") }}</span>
                </Link>
            </li>

            <!-- /******************************* notifications*********************************** */ -->
            <li class="nav-item" v-if="hasPermission('read notifications')">
                <Link class="nav-link" :href="route('notifications.index')"
                    :class="{ collapsed: !$page.url.startsWith('/notifications') }">
                <i class="bi bi-bell"></i>
                <span>{{ $t("notifications") }}</span>
                </Link>
            </li>

            <li class="nav-item" v-if="hasPermission('read advantages')">
                <Link class="nav-link" :href="route('advantages.index')"
                    :class="{ collapsed: !$page.url.startsWith('/advantages') }">
                <i class="bi bi-star"></i>
                <span>{{ $t("advantages") }}</span>
                </Link>
            </li>
            <!-- reports -->
            <!-- <li class="nav-item" v-if="hasPermission('read users')">
                <Link
                    class="nav-link"
                    :href="route('reports.index')"
                    :class="{ collapsed: !$page.url.startsWith('/admin/reports') }"
                >
                    <i class="bi bi-file-earmark"></i>
                    <span>{{ $t("reports") }}</span>
                </Link>
            </li> -->
            <!-- user activity report -->

            <!--
    <li class="nav-item" v-if="hasPermission('read logs')">
    <Link  class="nav-link "  :href="route('logs')"  :class="{ 'collapsed':  !$page.url.startsWith('/logs') }" >
            <i class="bi bi-database"></i>
            <span>{{$t('logs') }}</span>
               </Link>
    </li> -->

            <li class="nav-item" v-if="hasPermission('read static_pages')">
                <a class="nav-link collapsed" data-bs-target="#pages-dropdown" data-bs-toggle="collapse" href="#">
                    <i class="bi bi-file-earmark"></i>
                    <span>{{ $t("pages") }}</span>
                    <i class="bi bi-chevron-down ms-auto"></i>
                </a>
                <ul id="pages-dropdown" class="nav-content collapse" data-bs-parent="#sidebar">
                    <li v-if="hasPermission('read static_pages')">
                        <Link class="nav-link" :href="route('pages.edit', { slug: 'about-us' })" :class="{
                            collapsed: !$page.url.startsWith(
                                '/pages/about-us/edit'
                            ),
                        }">
                        <i class="bi bi-circle"></i>
                        <span>{{ $t("about_us") }}</span>
                        </Link>
                    </li>
                    <li v-if="hasPermission('read static_pages')">
                        <Link class="nav-link" :href="route('pages.edit', { slug: 'privacy-policy' })
                            " :class="{
                        collapsed: !$page.url.startsWith(
                            '/pages/privacy-policy/edit'
                        ),
                    }">
                        <i class="bi bi-circle"></i>
                        <span>{{ $t("privacy_policy") }}</span>
                        </Link>
                    </li>
                    <li v-if="hasPermission('read static_pages')">
                        <Link class="nav-link" :href="route('pages.edit', {
                            slug: 'terms-and-conditions',
                        })
                            " :class="{
                        collapsed: !$page.url.startsWith(
                            '/pages/terms-and-conditions/edit'
                        ),
                    }">
                        <i class="bi bi-circle"></i>
                        <span>{{ $t("terms_and_conditions") }}</span>
                        </Link>
                    </li>

                    <li v-if="hasPermission('read static_pages')">
                        <Link class="nav-link" :href="route('pages.edit', { slug: 'contact-us' })" :class="{
                            collapsed: !$page.url.startsWith(
                                '/pages/contact-us/edit'
                            ),
                        }">
                        <i class="bi bi-circle"></i>
                        <span>{{ $t("contact_us") }}</span>
                        </Link>
                    </li>
                    <li v-if="hasPermission('read static_pages')">
                        <Link class="nav-link" :href="route('faqs.index')" :class="{
                            collapsed: !$page.url.startsWith('/faqs'),
                        }">
                        <i class="bi bi-circle"></i>
                        <span>{{ $t("faqs") }}</span>
                        </Link>
                    </li>
                </ul>
            </li>

            <!-- settings -->
            <li class="nav-item" v-if="hasPermission('read settings')">
                <Link class="nav-link" :href="route('settings.index')"
                    :class="{ collapsed: !$page.url.startsWith('/settings') }">
                <i class="bi bi-gear"></i>
                <span>{{ $t("settings") }}</span>
                </Link>
            </li>

            <!-- reports -->
            <li class="nav-item">
                <Link class="nav-link" :href="route('about-us.edit')"
                    :class="{ collapsed: !$page.url.startsWith('/admin/about-us') }">
                <i class="bi bi-file-earmark"></i>
                <span>{{ $t("من نحن") }}</span>
                </Link>
            </li>
        </ul>
    </aside>
</template>

<script setup>
import { Link, usePage } from "@inertiajs/vue3";

const page = usePage();

const hasPermission = (permission) => {
    return page.props.auth_permissions.includes(permission);
};
defineProps({ message: String });
</script>

<script>
export default {
    name: "Sidebar",
};
</script>
