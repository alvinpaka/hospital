<script setup lang="ts">
import NavMain from '@/components/NavMain.vue';
import NavUser from '@/components/NavUser.vue';
import { Sidebar, SidebarContent, SidebarFooter, SidebarHeader, SidebarMenu, SidebarMenuButton, SidebarMenuItem } from '@/components/ui/sidebar';
import { type NavItem } from '@/types';
import { Link, usePage } from '@inertiajs/vue3';
import { Folder, LayoutGrid, Calendar, FileText, Users, DollarSign, Stethoscope, Activity } from 'lucide-vue-next';
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
    },
    {
        title: 'My Prescriptions',
        href: '/prescriptions',
        icon: FileText,
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

const staffNavItems: NavItem[] = [
    {
        title: 'Staff Dashboard',
        href: '/staff/dashboard',
        icon: LayoutGrid,
    },
    {
        title: 'My Appointments',
        href: '/staff/appointments',
        icon: Calendar,
    },
    {
        title: 'My Patients',
        href: '/staff/patients',
        icon: Users,
    },
    {
        title: 'My Prescriptions',
        href: '/staff/prescriptions',
        icon: FileText,
    },
    {
        title: 'My Lab Results',
        href: '/staff/lab-results',
        icon: Folder,
    },
    {
        title: 'My Vitals',
        href: '/staff/vitals',
        icon: Activity,
    },
    {
        title: 'My Visits',
        href: '/staff/visits',
        icon: Stethoscope,
    },
];

const mainNavItems: NavItem[] = user?.role === 'patient' ? patientNavItems : 
                                user?.role === 'admin' ? adminNavItems : 
                                ['doctor', 'nurse'].includes(user?.role) ? staffNavItems : [
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
