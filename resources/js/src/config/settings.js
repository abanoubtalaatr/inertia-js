export default {
    supportedLanguages: ['ar', 'en', 'fr', 'fil', 'ur']

};
export const getImageUrl = (path) => {
    if (!path) {
        return "/dashboard-assets/img/default-avatar.png"; // الصورة الافتراضية
    }
    return `${import.meta.env.VITE_BASE_URL}/storage/${path}`;
};