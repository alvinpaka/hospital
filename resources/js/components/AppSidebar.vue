<script setup lang="ts">
import NavMain from '@/components/NavMain.vue';
import NavUser from '@/components/NavUser.vue';
import { Sidebar, SidebarContent, SidebarFooter, SidebarHeader, SidebarMenu, SidebarMenuButton, SidebarMenuItem } from '@/components/ui/sidebar';
import { type NavItem } from '@/types';
import { Link, usePage } from '@inertiajs/vue3';
import { Folder, LayoutGrid, Calendar, FileText, Users, DollarSign } from 'lucide-vue-next';
import AppLogo from './AppLogo.vue';
import { usePrescriptionBlink } from '@/composables/usePrescriptionBlink';
import { useAppointmentBlink } from '@/composables/useAppointmentBlink';
import AppearanceTabs from '@/components/AppearanceTabs.vue';

const page = usePage();
const user = page.props.auth.user;
const { prescriptionBlink } = usePrescriptionBlink();
const { appointmentBlink } = useAppointmentBlink();

const patientNavItems: NavItem[] = [
    {
        title: 'Dashboard',
        href: '/dashboard',
        icon: LayoutGrid,
    },
    {
        title: 'My Appointments',
        href: '/appointments',
        icon: Calendar,
        blink: appointmentBlink,
    },
    {
        title: 'My Prescriptions',
        href: '/prescriptions',
        icon: FileText,
        blink: prescriptionBlink,
    },
    {
        title: 'My Lab Results',
        href: '/lab-results',
        icon: Calendar,
    },
    {
        title: 'My Vitals',
        href: '/vitals',
        icon: FileText,
    },
    {
        title: 'My Visits',
        href: '/visits',
        icon: Calendar,
    },
    {
        title: 'My Billings',
        href: '/my-billings',
        icon: DollarSign,
    },
];

const adminNavItems: NavItem[] = [
    {
        title: 'Admin Dashboard',
        href: '/admin/dashboard',
        icon: LayoutGrid,
    },
    {
        title: 'Patient Management',
        href: '/patient-management',
        icon: Users,
    },
    {
        title: 'Staff Management',
        href: '/staff-management',
        icon: Users,
    },
    {
        title: 'Admin Appointments',
        href: '/appointments',
        icon: Calendar,
        blink: appointmentBlink,
    },
    {
        title: 'Admin Prescriptions',
        href: '/admin/prescriptions',
        icon: FileText,
    },
    {
        title: 'Admin Lab Results',
        href: '/admin/lab-results',
        icon: Folder,
    },
    {
        title: 'Admin Vitals',
        href: '/admin/vitals',
        icon: FileText,
    },
    {
        title: 'Admin Visits',
        href: '/admin/visits',
        icon: Calendar,
    },
    {
        title: 'Billing & Invoicing',
        href: '/admin/billings',
        icon: DollarSign,
    },
];

const mainNavItems: NavItem[] = user?.role === 'patient' ? patientNavItems : 
                                user?.role === 'admin' ? adminNavItems : [
    {
        title: 'Dashboard',
        href: '/dashboard',
        icon: LayoutGrid,
    },
];

</script>

<template>
    <Sidebar collapsible="icon" variant="inset">
        <SidebarHeader>
            <SidebarMenu>
                <SidebarMenuItem>
                    <SidebarMenuButton size="lg" as-child>
                        <Link :href="route('dashboard')">
                            <AppLogo />
                        </Link>
                    </SidebarMenuButton>
                </SidebarMenuItem>
            </SidebarMenu>
        </SidebarHeader>

        <SidebarContent>
            <NavMain :items="mainNavItems" />
            <NavUser />
        </SidebarContent>

        <SidebarFooter>
            
            <AppearanceTabs />
        </SidebarFooter>
    </Sidebar>
    <slot />
</template>
