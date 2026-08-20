import forms from '@tailwindcss/forms';

export default {
    content: [
        './resources/**/*.blade.php',
        './resources/**/*.js',
        './app/Filament/**/*.php',
    ],
    theme: {
        extend: {
            colors: {
                tealTrust: '#0D7D74',
                navyDeep: '#0F2942',
                accent: '#E07B39',
                successCare: '#1F8F5F',
                cloud: '#F5F8F9',
            },
            fontFamily: {
                sans: ['Inter', 'Noto Sans Bengali', 'ui-sans-serif', 'system-ui'],
                bangla: ['Noto Sans Bengali', 'Hind Siliguri', 'ui-sans-serif'],
            },
        },
    },
    plugins: [forms],
};
