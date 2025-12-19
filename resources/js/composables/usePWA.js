import { ref, onMounted } from 'vue';

export function usePWA() {
    const deferredPrompt = ref(null);
    const showInstallPrompt = ref(false);
    const isInstalled = ref(false);

    // Check if app is already installed
    const checkIfInstalled = () => {
        // Check if running in standalone mode
        if (window.matchMedia('(display-mode: standalone)').matches) {
            isInstalled.value = true;
            return true;
        }
        // Check for iOS standalone
        if (window.navigator.standalone === true) {
            isInstalled.value = true;
            return true;
        }
        return false;
    };

    // Install the PWA
    const installPWA = async () => {
        if (!deferredPrompt.value) {
            console.log('Install prompt not available');
            return false;
        }

        // Show the install prompt
        deferredPrompt.value.prompt();

        // Wait for the user to respond to the prompt
        const { outcome } = await deferredPrompt.value.userChoice;

        console.log(`User response to install prompt: ${outcome}`);

        if (outcome === 'accepted') {
            isInstalled.value = true;
            showInstallPrompt.value = false;
        }

        // Clear the deferredPrompt
        deferredPrompt.value = null;

        return outcome === 'accepted';
    };

    // Dismiss the install prompt
    const dismissInstall = () => {
        showInstallPrompt.value = false;
        localStorage.setItem('pwa-install-dismissed', Date.now().toString());
    };

    onMounted(() => {
        // Check if already installed
        if (checkIfInstalled()) {
            return;
        }

        // Check if user dismissed recently (within 7 days)
        const dismissed = localStorage.getItem('pwa-install-dismissed');
        if (dismissed) {
            const dismissedTime = parseInt(dismissed);
            const sevenDays = 7 * 24 * 60 * 60 * 1000;
            if (Date.now() - dismissedTime < sevenDays) {
                return;
            }
        }

        // Listen for the beforeinstallprompt event
        window.addEventListener('beforeinstallprompt', (e) => {
            // Prevent the default browser prompt
            e.preventDefault();

            // Store the event for later use
            deferredPrompt.value = e;

            // Show our custom install prompt
            showInstallPrompt.value = true;

            console.log('PWA install prompt ready');
        });

        // Listen for successful installation
        window.addEventListener('appinstalled', () => {
            console.log('PWA installed successfully');
            isInstalled.value = true;
            showInstallPrompt.value = false;
            deferredPrompt.value = null;
        });
    });

    return {
        deferredPrompt,
        showInstallPrompt,
        isInstalled,
        installPWA,
        dismissInstall,
    };
}
