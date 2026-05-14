export const settings = {
    title: 'Settings',
    subtitle: 'Manage your account settings and privacy',
    sections: {
        account: {
            label: 'Account',
            desc: 'Personal information and security'
        },
        privacy: {
            label: 'Privacy',
            desc: 'Activity control'
        },
        language: {
            label: 'Language',
            desc: 'Select your language'
        },
        cookies: {
            label: 'Cookies & Analytics',
            desc: 'Tracking preferences'
        },
        notifications: {
            label: 'Notifications',
            desc: 'Alerts and notices'
        },
        tutors: {
            label: 'Legal Tutors',
            desc: 'Family links'
        }
    },
    account: {
        title: 'Account',
        email_label: 'Email',
        status_label: 'Status',
        status_active: 'Active'
    },
    tutors: {
        title: 'Legal Tutors',
        dni_placeholder: 'Enter tutor DNI',
        add_button: 'Search and Add',
        no_tutors: 'You have no linked tutors.',
        tutor_name: 'Tutor name',
        remove_confirm: 'Are you sure you want to remove this tutor?',
        search_error: 'No tutor found with that DNI or not a valid tutor.',
        add_success: 'Tutor added successfully.',
        remove_modal: {
            title: 'Delete tutor?',
            description: 'This action will permanently unbind this tutor from your account.',
            confirm: 'Delete',
            cancel: 'Cancel'
        }
    },
    privacy: {
        title: 'Privacy',
        coming_soon: 'Coming soon: Profile visibility controls and direct messages.'
    },
    language: {
        title: 'Language',
        spanish: 'Spanish',
        english: 'English'
    },
    cookies: {
        title: 'Cookies & Analytics',
        desc: 'We use Umami to collect anonymous usage data and improve our platform. We do not use invasive tracking cookies or sell your data.',
        tracking_label: 'Cookie tracking',
        tracking_on: 'Analytics are enabled. Thank you for helping us improve.',
        tracking_off: 'Tracking is disabled.',
        accept_all: 'Accept all',
        reject_optional: 'Reject optional'
    },
    notifications: {
        title: 'Notifications',
        coming_soon: 'Coming soon: Configure your email alerts and push notifications.'
    }
}
