/**
 * NEVORA Web3 & Multi-Platform Integration Engine (B TITAN Experience)
 * Supports all 9 major Web3 wallet platforms:
 * 1. SafePal (Recommended)
 * 2. Trust Wallet (Popular)
 * 3. MetaMask (EVM Standard)
 * 4. Binance Web3 Wallet (Exchange Web3)
 * 5. OKX Wallet (Multi-Chain)
 * 6. Bitget Wallet (BitKeep)
 * 7. TokenPocket (DeFi Native)
 * 8. Coinbase Wallet (Global)
 * 9. WalletConnect / Other
 */

const NevoraWeb3 = (function () {
    // Inject responsive modal styling dynamically
    if (typeof document !== 'undefined') {
        const style = document.createElement('style');
        style.textContent = `
            .nevora-wallet-modal-popup {
                max-width: 95vw !important;
                box-sizing: border-box !important;
                margin: auto !important;
            }
            .nevora-wallet-modal-popup .swal2-html-container {
                margin: 0 !important;
                padding: 0 !important;
                overflow-x: hidden !important;
            }
            @media (max-width: 576px) {
                .nevora-wallet-modal-popup {
                    padding: 14px 10px !important;
                }
                .nevora-wallet-modal-popup .wallet-option-card {
                    padding: 9px 7px !important;
                }
                .nevora-wallet-modal-popup .wallet-icon-box {
                    width: 38px !important;
                    height: 38px !important;
                    margin-bottom: 6px !important;
                }
                .nevora-wallet-modal-popup .wallet-icon-box svg {
                    width: 26px !important;
                    height: 26px !important;
                }
            }
        `;
        document.head.appendChild(style);
    }

    // Core Blockchain Configuration
    const state = {
        chainId: 56, // BSC Mainnet
        chainIdHex: '0x38',
        chainName: 'BNB Smart Chain',
        rpcUrl: 'https://bsc-dataseed.binance.org',
        blockExplorer: 'https://bscscan.com',
        usdtContractAddress: '0x55d398326f99059fF775485246999027B3197955',
        connectedAddress: null,
        connectedPlatform: null,
        provider: null,
        signer: null,
    };

    // Minimal BEP-20 ABI
    const BEP20_ABI = [
        "function name() view returns (string)",
        "function symbol() view returns (string)",
        "function decimals() view returns (uint8)",
        "function balanceOf(address owner) view returns (uint256)",
        "function transfer(address to, uint256 amount) returns (bool)",
        "event Transfer(address indexed from, address indexed to, uint256 value)"
    ];

    /**
     * Modular Web3 Wallet Platforms Roster (All 9 Platforms)
     * Easily enable, disable, or reorder based on preference.
     */
    const SUPPORTED_WALLETS = [
        {
            id: 'safepal',
            name: 'SafePal',
            tagline: 'Hardware & App Wallet',
            badge: 'Recommended',
            badgeClass: 'bg-primary text-white',
            accentColor: '#1d4ed8',
            iconSvg: `<svg width="28" height="28" viewBox="0 0 40 40" fill="none" xmlns="http://www.w3.org/2000/svg"><rect width="40" height="40" rx="10" fill="#1D4ED8"/><path d="M12 15C12 13.3431 13.3431 12 15 12H21C22.6569 12 24 13.3431 24 15V18H15C13.3431 18 12 16.6569 12 15Z" fill="white"/><path d="M28 25C28 26.6569 26.6569 28 25 28H19C17.3431 28 16 26.6569 16 25V22H25C26.6569 22 28 23.3431 28 25Z" fill="#93C5FD"/><path d="M24 18V22H28V18H24Z" fill="white"/></svg>`,
            getProvider: () => window.safepalProvider || (window.ethereum?.isSafePal ? window.ethereum : null),
            getDeepLink: (url) => `https://link.safepal.io/dapp?url=${encodeURIComponent(url)}`,
        },
        {
            id: 'trust',
            name: 'Trust Wallet',
            tagline: 'Binance Multi-Chain Wallet',
            badge: 'Popular',
            badgeClass: 'bg-info text-white',
            accentColor: '#0500ff',
            iconSvg: `<svg width="28" height="28" viewBox="0 0 40 40" fill="none" xmlns="http://www.w3.org/2000/svg"><rect width="40" height="40" rx="10" fill="#0500FF"/><path d="M20 10L28 14V22C28 26.5 24.5 29.8 20 31C15.5 29.8 12 26.5 12 22V14L20 10Z" stroke="white" stroke-width="2.5" stroke-linejoin="round"/></svg>`,
            getProvider: () => window.trustwallet || (window.ethereum?.isTrust ? window.ethereum : null),
            getDeepLink: (url) => `https://link.trustwallet.com/open_url?coin_id=60&url=${encodeURIComponent(url)}`,
        },
        {
            id: 'metamask',
            name: 'MetaMask',
            tagline: 'EVM Standard Web3 Wallet',
            badge: 'EVM Standard',
            badgeClass: 'bg-warning text-dark',
            accentColor: '#e2761b',
            iconSvg: `<svg width="28" height="28" viewBox="0 0 40 40" fill="none" xmlns="http://www.w3.org/2000/svg"><rect width="40" height="40" rx="10" fill="#FFF2E8"/><path d="M29.5 13L20 19.5L22.5 14L29.5 13Z" fill="#E2761B" stroke="#E2761B" stroke-width="0.5"/><path d="M10.5 13L20 19.5L17.5 14L10.5 13Z" fill="#E4761B" stroke="#E4761B" stroke-width="0.5"/><path d="M26.8 24.2L24.5 27.5L29 28.8L30 24L26.8 24.2Z" fill="#E4761B"/><path d="M13.2 24.2L15.5 27.5L11 28.8L10 24L13.2 24.2Z" fill="#E4761B"/><path d="M20 22L17.5 25.5H22.5L20 22Z" fill="#D7C1B3"/></svg>`,
            getProvider: () => {
                if (window.ethereum?.isMetaMask && !window.ethereum?.isSafePal && !window.ethereum?.isTrust && !window.ethereum?.isTokenPocket) {
                    return window.ethereum;
                }
                return null;
            },
            getDeepLink: (url) => {
                const clean = url.replace(/^https?:\/\//, '');
                return `https://metamask.app.link/dapp/${clean}`;
            },
        },
        {
            id: 'binance',
            name: 'Binance Web3',
            tagline: 'Binance App Integrated Wallet',
            badge: 'Exchange Web3',
            badgeClass: 'bg-warning bg-opacity-75 text-dark',
            accentColor: '#f0b90b',
            iconSvg: `<svg width="28" height="28" viewBox="0 0 40 40" fill="none" xmlns="http://www.w3.org/2000/svg"><rect width="40" height="40" rx="10" fill="#181A20"/><path d="M20 11L24 15L20 19L16 15L20 11Z" fill="#F0B90B"/><path d="M25.5 16.5L29.5 20.5L25.5 24.5L21.5 20.5L25.5 16.5Z" fill="#F0B90B"/><path d="M14.5 16.5L18.5 20.5L14.5 24.5L10.5 20.5L14.5 16.5Z" fill="#F0B90B"/><path d="M20 22L24 26L20 30L16 26L20 22Z" fill="#F0B90B"/></svg>`,
            getProvider: () => window.binance || (window.ethereum?.isBinance ? window.ethereum : null),
            getDeepLink: (url) => `https://link.safepal.io/dapp?url=${encodeURIComponent(url)}`, // fallback
        },
        {
            id: 'okx',
            name: 'OKX Wallet',
            tagline: 'Multi-Chain Web3 Portal',
            badge: 'Multi-Chain',
            badgeClass: 'bg-dark text-white',
            accentColor: '#000000',
            iconSvg: `<svg width="28" height="28" viewBox="0 0 40 40" fill="none" xmlns="http://www.w3.org/2000/svg"><rect width="40" height="40" rx="10" fill="#000000"/><rect x="13" y="13" width="6" height="6" fill="white"/><rect x="21" y="13" width="6" height="6" fill="white"/><rect x="17" y="17" width="6" height="6" fill="white"/><rect x="13" y="21" width="6" height="6" fill="white"/><rect x="21" y="21" width="6" height="6" fill="white"/></svg>`,
            getProvider: () => window.okxwallet?.ethereum || (window.ethereum?.isOkxWallet ? window.ethereum : null),
            getDeepLink: (url) => `okx://wallet/dapp/url?dappUrl=${encodeURIComponent(url)}`,
        },
        {
            id: 'bitget',
            name: 'Bitget Wallet',
            tagline: 'Formerly BitKeep Web3',
            badge: 'Trending',
            badgeClass: 'bg-secondary text-white',
            accentColor: '#00d2d3',
            iconSvg: `<svg width="28" height="28" viewBox="0 0 40 40" fill="none" xmlns="http://www.w3.org/2000/svg"><rect width="40" height="40" rx="10" fill="#7530FF"/><path d="M14 16L20 12L26 16V24L20 28L14 24V16Z" fill="#00F0FF"/></svg>`,
            getProvider: () => window.bitkeep?.ethereum || (window.ethereum?.isBitKeep ? window.ethereum : null),
            getDeepLink: (url) => `https://bkcode.vip?action=dapp&url=${encodeURIComponent(url)}`,
        },
        {
            id: 'tokenpocket',
            name: 'TokenPocket',
            tagline: 'DeFi Multi-Chain Native',
            badge: 'DeFi Native',
            badgeClass: 'bg-primary bg-opacity-75 text-white',
            accentColor: '#2980fe',
            iconSvg: `<svg width="28" height="28" viewBox="0 0 40 40" fill="none" xmlns="http://www.w3.org/2000/svg"><rect width="40" height="40" rx="10" fill="#2980FE"/><path d="M14 13H26V18H19V22H25V27H14V13Z" fill="white"/></svg>`,
            getProvider: () => window.tokenpocket?.ethereum || (window.ethereum?.isTokenPocket ? window.ethereum : null),
            getDeepLink: (url) => `tpdapp://open?params={"url":"${encodeURIComponent(url)}"}`,
        },
        {
            id: 'coinbase',
            name: 'Coinbase Wallet',
            tagline: 'Global Web3 Self-Custody',
            badge: 'Global',
            badgeClass: 'bg-primary text-white',
            accentColor: '#0052ff',
            iconSvg: `<svg width="28" height="28" viewBox="0 0 40 40" fill="none" xmlns="http://www.w3.org/2000/svg"><rect width="40" height="40" rx="10" fill="#0052FF"/><rect x="14" y="14" width="12" height="12" rx="3" fill="white"/><rect x="17" y="17" width="6" height="6" rx="1.5" fill="#0052FF"/></svg>`,
            getProvider: () => window.coinbaseWalletExtension || (window.ethereum?.isCoinbaseWallet ? window.ethereum : null),
            getDeepLink: (url) => `https://go.cb-w.com/dapp?cb_url=${encodeURIComponent(url)}`,
        },
        {
            id: 'walletconnect',
            name: 'WalletConnect / Injected',
            tagline: 'Browser Extension & Other',
            badge: 'Universal',
            badgeClass: 'bg-light text-dark border',
            accentColor: '#3b99fc',
            iconSvg: `<svg width="28" height="28" viewBox="0 0 40 40" fill="none" xmlns="http://www.w3.org/2000/svg"><rect width="40" height="40" rx="10" fill="#3B99FC"/><path d="M14 18C17.3 14.7 22.7 14.7 26 18L26.8 18.8C27 19 27 19.3 26.8 19.5L25.3 21C25.2 21.1 25 21.1 24.9 21L23.8 19.9C21.7 17.8 18.3 17.8 16.2 19.9L15.1 21C15 21.1 14.8 21.1 14.7 21L13.2 19.5C13 19.3 13 19 13.2 18.8L14 18Z" fill="white"/><path d="M28.4 20.4L29.9 21.9C30.1 22.1 30.1 22.4 29.9 22.6L23.9 28.6C23.7 28.8 23.4 28.8 23.2 28.6L19.9 25.3C19.8 25.2 19.6 25.2 19.5 25.3L16.2 28.6C16 28.8 15.7 28.8 15.5 28.6L9.5 22.6C9.3 22.4 9.3 22.1 9.5 21.9L11 20.4C11.2 20.2 11.5 20.2 11.7 20.4L15 23.7C15.1 23.8 15.3 23.8 15.4 23.7L18.7 20.4C18.9 20.2 19.2 20.2 19.4 20.4L22.7 23.7C22.8 23.8 23 23.8 23.1 23.7L26.4 20.4C26.6 20.2 26.9 20.2 27.1 20.4L28.4 20.4Z" fill="white"/></svg>`,
            getProvider: () => window.ethereum || null,
            getDeepLink: (url) => url,
        }
    ];

    function isMobileDevice() {
        return /Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent);
    }

    /**
     * Detect which wallet platform is actively injected in the browser
     */
    function getDetectedWalletId() {
        if (typeof window === 'undefined') return null;
        if (window.safepalProvider || window.ethereum?.isSafePal) return 'safepal';
        if (window.trustwallet || window.ethereum?.isTrust) return 'trust';
        if (window.okxwallet?.ethereum || window.ethereum?.isOkxWallet) return 'okx';
        if (window.bitkeep?.ethereum || window.ethereum?.isBitKeep) return 'bitget';
        if (window.tokenpocket?.ethereum || window.ethereum?.isTokenPocket) return 'tokenpocket';
        if (window.binance || window.ethereum?.isBinance) return 'binance';
        if (window.ethereum?.isMetaMask) return 'metamask';
        if (window.coinbaseWalletExtension || window.ethereum?.isCoinbaseWallet) return 'coinbase';
        if (window.ethereum) return 'walletconnect';
        return null;
    }

    /**
     * Resolve provider for a given wallet platform with graceful fallback
     */
    function resolveProvider(walletId = null) {
        if (walletId) {
            const wallet = SUPPORTED_WALLETS.find(w => w.id === walletId);
            if (wallet) {
                const prov = wallet.getProvider();
                if (prov) return prov;
            }
        }

        // Generic detection hierarchy
        if (window.safepalProvider) return window.safepalProvider;
        if (window.trustwallet) return window.trustwallet;
        if (window.okxwallet?.ethereum) return window.okxwallet.ethereum;
        if (window.bitkeep?.ethereum) return window.bitkeep.ethereum;
        if (window.tokenpocket?.ethereum) return window.tokenpocket.ethereum;
        if (window.binance) return window.binance;
        if (window.coinbaseWalletExtension) return window.coinbaseWalletExtension;
        if (window.ethereum) {
            if (window.ethereum.providers?.length) {
                return window.ethereum.providers[0];
            }
            return window.ethereum;
        }
        return null;
    }

    /**
     * Switch network to BSC Mainnet (Chain ID 56)
     */
    async function checkAndSwitchNetwork(provider) {
        try {
            const currentChainId = await provider.request({ method: 'eth_chainId' });
            if (parseInt(currentChainId, 16) !== state.chainId) {
                try {
                    await provider.request({
                        method: 'wallet_switchEthereumChain',
                        params: [{ chainId: state.chainIdHex }]
                    });
                } catch (switchError) {
                    if (switchError.code === 4902) {
                        await provider.request({
                            method: 'wallet_addEthereumChain',
                            params: [{
                                chainId: state.chainIdHex,
                                chainName: state.chainName,
                                nativeCurrency: { name: 'BNB', symbol: 'BNB', decimals: 18 },
                                rpcUrls: [state.rpcUrl],
                                blockExplorerUrls: [state.blockExplorer]
                            }]
                        });
                    } else {
                        throw switchError;
                    }
                }
            }
        } catch (error) {
            console.warn('Network switch warning:', error);
        }
    }

    /**
     * Connect to wallet directly by platform ID
     * @param {string|null} walletId 
     * @param {string|null} targetDeepLinkUrl Custom deep link URL for mobile wallet dApp browser handshakes
     */
    async function connectByPlatform(walletId = null, targetDeepLinkUrl = null) {
        const detectedId = getDetectedWalletId();
        const effectiveId = walletId || detectedId || 'safepal';
        const wallet = SUPPORTED_WALLETS.find(w => w.id === effectiveId) || SUPPORTED_WALLETS[0];
        const provider = resolveProvider(effectiveId) || resolveProvider();

        if (!provider) {
            if (isMobileDevice() && wallet.getDeepLink) {
                const targetUrl = targetDeepLinkUrl || window.location.href;
                const link = wallet.getDeepLink(targetUrl);
                window.location.href = link;
                return { isRedirecting: true, deepLink: link, wallet };
            } else {
                throw new Error(`${wallet.name} extension or app not detected in your browser.`);
            }
        }

        const accounts = await provider.request({ method: 'eth_requestAccounts' });
        if (!accounts || accounts.length === 0) {
            throw new Error('No accounts selected in wallet.');
        }

        const address = accounts[0].toLowerCase();
        state.connectedAddress = address;
        state.connectedPlatform = wallet.name;

        await checkAndSwitchNetwork(provider);

        if (window.ethers) {
            state.provider = new ethers.BrowserProvider(provider);
            state.signer = await state.provider.getSigner();
        }

        return { address, wallet, provider };
    }

    /**
     * Generate HTML for the 9-wallet selector grid (Mobile-Optimized & Responsive)
     */
    function renderWalletGridHtml(actionPrompt = 'Connect to link with NEVORA') {
        const isMobile = isMobileDevice();
        let cardsHtml = '';

        SUPPORTED_WALLETS.forEach(w => {
            const isDetected = !!w.getProvider();
            const detectedBadge = isDetected 
                ? '<span class="badge bg-success bg-opacity-10 text-success font-9 font-sm-10 px-2 py-0.5 rounded-pill"><i class="fa-solid fa-circle-check me-1"></i>Detected</span>'
                : `<span class="badge ${w.badgeClass} font-9 font-sm-10 px-1.5 px-sm-2 py-0.5 rounded-pill">${w.badge}</span>`;

            cardsHtml += `
                <div class="col-6 col-md-4">
                    <div class="wallet-option-card p-2 p-sm-3 rounded-4 border text-center h-100 cursor-pointer position-relative transition-all"
                         onclick="NevoraWeb3.onWalletCardClicked('${w.id}')"
                         data-wallet-id="${w.id}"
                         style="background: #ffffff; cursor: pointer; transition: all 0.2s ease;">
                        <div class="d-flex justify-content-between align-items-center mb-1 mb-sm-2">
                            ${detectedBadge}
                            <i class="fa-solid fa-chevron-right font-9 font-sm-10 text-muted opacity-50"></i>
                        </div>
                        <div class="wallet-icon-box mb-1 mb-sm-2 d-flex align-items-center justify-content-center mx-auto" style="width: 40px; height: 40px;">
                            ${w.iconSvg}
                        </div>
                        <h6 class="fw-bold mb-0 text-dark font-12 font-sm-13 text-truncate">${w.name}</h6>
                        <small class="text-muted font-10 font-sm-11 d-block mt-0.5 text-truncate">${w.tagline}</small>
                    </div>
                </div>
            `;
        });

        return `
            <div class="px-1 py-1">
                <p class="text-muted font-12 mb-2 mb-sm-3 text-center">${actionPrompt}</p>
                <div class="row g-2 g-sm-2.5 text-start" id="wallet-selector-grid">
                    ${cardsHtml}
                </div>
                <div class="mt-2.5 mt-sm-3 text-center">
                    <small class="text-muted font-10 font-sm-11">
                        <i class="fa-solid fa-shield-halved text-primary me-1"></i>
                        Encrypted BSC BEP-20 Web3 Connection. No private keys are ever shared.
                    </small>
                </div>
            </div>
        `;
    }

    // Modal Callback Holder
    let currentModalCallback = null;

    /**
     * Show the All-Platforms B TITAN Wallet Selector Modal
     */
    function showWalletModal(options = {}) {
        const title = options.title || 'Choose Web3 Wallet Platform';
        const subtitle = options.subtitle || 'Select your wallet app to link with your NEVORA account';
        currentModalCallback = options.onSelect;
        const isMobile = window.innerWidth < 640;

        Swal.fire({
            title: `<span class="fw-bold text-dark font-16 font-sm-18">${title}</span>`,
            html: renderWalletGridHtml(subtitle),
            showConfirmButton: false,
            showCloseButton: true,
            width: isMobile ? '94%' : '640px',
            customClass: {
                popup: 'rounded-4 p-2.5 p-sm-4 shadow-lg border nevora-wallet-modal-popup',
                htmlContainer: 'm-0 p-0 overflow-hidden',
            }
        });
    }

    /**
     * Handle Card Click from Modal
     */
    async function onWalletCardClicked(walletId) {
        const wallet = SUPPORTED_WALLETS.find(w => w.id === walletId);
        if (!wallet) return;

        // Visual feedback on card
        const card = document.querySelector(`[data-wallet-id="${walletId}"]`);
        if (card) {
            card.style.borderColor = '#2563eb';
            card.style.backgroundColor = 'rgba(37, 99, 235, 0.05)';
        }

        if (currentModalCallback) {
            // Hand off to custom callback (e.g. registration submit or payment)
            currentModalCallback(walletId, wallet);
            return;
        }

        // Default behavior: connect & bind
        try {
            Swal.fire({
                title: `Connecting to ${wallet.name}...`,
                html: '<i class="fa-solid fa-spinner fa-spin fa-2x text-primary my-3"></i><p class="font-12 text-muted">Please approve the connection in your wallet app.</p>',
                allowOutsideClick: false,
                showConfirmButton: false,
                showCancelButton: true,
                cancelButtonText: 'Cancel',
            });

            const res = await connectByPlatform(walletId);
            if (res && res.isRedirecting) {
                Swal.fire({
                    icon: 'info',
                    title: `Launching ${wallet.name}...`,
                    html: `
                        <p class="font-12 text-muted mb-2">We opened <strong>${wallet.name}</strong> on your device.</p>
                        <p class="font-12 text-muted mb-0">Please unlock your app and approve the connection permission.</p>
                    `,
                    showConfirmButton: true,
                    confirmButtonText: 'Re-open ' + wallet.name,
                    showCancelButton: true,
                    cancelButtonText: 'Close',
                    confirmButtonColor: '#2563eb'
                }).then((action) => {
                    if (action.isConfirmed && res.deepLink) {
                        window.location.href = res.deepLink;
                    }
                });
                return;
            }

            if (res && res.address) {
                Swal.fire({
                    icon: 'success',
                    title: `${wallet.name} Connected!`,
                    html: `<p class="font-mono font-12 text-muted">${res.address}</p>`,
                    timer: 1500,
                    showConfirmButton: false
                });
            }
        } catch (e) {
            Swal.fire({
                icon: 'error',
                title: 'Connection Failed',
                text: e.message || 'Could not connect to wallet.',
                confirmButtonText: 'Try Again'
            });
        }
    }

    /**
     * 1-Click Multi-Wallet Payment Flow (Dashboard Activation)
     */
    async function openPaymentModal(slotNumber = 1) {
        try {
            // 1. Fetch beneficiary routing info from Laravel backend
            Swal.fire({
                title: 'Resolving Payment Route...',
                html: '<i class="fa-solid fa-spinner fa-spin fa-2x text-primary my-3"></i><p class="font-12 text-muted">Calculating 3-matrix eligible beneficiary...</p>',
                allowOutsideClick: false,
                showConfirmButton: false,
            });

            const routeRes = await fetch(`/member/payment-route-info?slot_number=${slotNumber}`, {
                headers: { 'Accept': 'application/json' }
            });
            const routeData = await routeRes.json();

            if (!routeData.status) {
                Swal.fire({
                    icon: 'error',
                    title: 'Payment Route Error',
                    text: routeData.message || 'Could not resolve payee route.',
                });
                return;
            }

            const payeeAddress = routeData.payee_address;
            const payeeName = routeData.payee_name || 'Eligible Beneficiary';
            const payeeMemberId = routeData.payee_member_id || 'COMPANY01';
            const payeeMasked = routeData.payee_masked_address || (payeeAddress ? (payeeAddress.slice(0, 6) + '...' + payeeAddress.slice(-4)) : '-');
            const amountUsdt = routeData.amount;
            const amountWei = BigInt(routeData.amount_wei);
            const usdtContractAddr = routeData.usdt_contract || state.usdtContractAddress;

            // 2. Display B TITAN Multi-Wallet Payment Modal
            const isAlreadyConnected = !!state.connectedAddress;
            const connectedWalletName = state.connectedPlatform || 'Connected Wallet';

            let connectedPayBtn = '';
            if (isAlreadyConnected) {
                connectedPayBtn = `
                    <div class="p-3 rounded-4 bg-success bg-opacity-10 border border-success border-opacity-25 mb-3 text-start">
                        <div class="d-flex align-items-center justify-content-between mb-2">
                            <span class="badge bg-success text-white font-11 px-2.5 py-1 rounded-pill">
                                <i class="fa-solid fa-circle-check me-1"></i> ${connectedWalletName} Ready
                            </span>
                            <span class="font-monospace font-11 text-muted">${state.connectedAddress.slice(0, 6)}...${state.connectedAddress.slice(-4)}</span>
                        </div>
                        <button type="button" class="btn btn-success w-100 py-2.5 rounded-pill fw-bold shadow-sm d-flex align-items-center justify-content-center gap-2"
                                onclick="NevoraWeb3.executeTransfer('${payeeAddress}', '${amountWei}', ${slotNumber}, '${payeeName}', '${payeeMemberId}', '${amountUsdt}')">
                            <i class="fa-solid fa-bolt text-warning"></i>
                            <span>Authorize $${parseFloat(amountUsdt).toFixed(2)} USDT with ${connectedWalletName}</span>
                        </button>
                    </div>
                    <div class="position-relative text-center my-3">
                        <hr class="border-secondary border-opacity-25 my-0">
                        <span class="position-absolute top-50 start-50 translate-middle bg-white px-3 font-11 text-muted fw-semibold text-uppercase">Or Choose Another Platform</span>
                    </div>
                `;
            }

            const headerSummaryHtml = `
                <div class="p-3 bg-light rounded-4 text-start mb-3 border">
                    <div class="d-flex align-items-center justify-content-between p-2 mb-2 rounded-3 bg-primary bg-opacity-10 border border-primary border-opacity-25">
                        <div>
                            <span class="text-muted font-10 text-uppercase d-block fw-semibold">Slot ${slotNumber} Activation</span>
                            <h5 class="fw-bold text-primary mb-0 font-16">$${parseFloat(amountUsdt).toFixed(2)} <span class="font-12 fw-normal">USDT (BEP-20)</span></h5>
                        </div>
                        <span class="badge bg-primary text-white font-11 px-2.5 py-1 rounded-pill">BNB Smart Chain</span>
                    </div>
                    <div class="p-2.5 bg-white rounded-3 border">
                        <div class="d-flex justify-content-between font-12 mb-1 pb-1 border-bottom border-light">
                            <span class="text-muted">Payee Member ID:</span>
                            <span class="fw-bold font-monospace text-primary">${payeeMemberId}</span>
                        </div>
                        <div class="d-flex justify-content-between font-12">
                            <span class="text-muted">Payee Wallet:</span>
                            <span class="fw-bold font-monospace text-dark">${payeeMasked}</span>
                        </div>
                    </div>
                </div>
            `;

            const isPaymentModalMobile = window.innerWidth < 640;
            Swal.fire({
                title: `<span class="fw-bold text-dark font-16 font-sm-18">Pay $${parseFloat(amountUsdt).toFixed(2)} USDT to Activate</span>`,
                html: `
                    ${headerSummaryHtml}
                    ${connectedPayBtn}
                    ${renderWalletGridHtml('Select your wallet app to authorize payment')}
                `,
                showConfirmButton: false,
                showCloseButton: true,
                width: isPaymentModalMobile ? '94%' : '640px',
                customClass: {
                    popup: 'rounded-4 p-2.5 p-sm-4 shadow-lg border nevora-wallet-modal-popup',
                    htmlContainer: 'm-0 p-0 overflow-hidden',
                }
            });

            // Set modal callback to execute transfer after connecting chosen wallet
            currentModalCallback = async (walletId, wallet) => {
                try {
                    Swal.fire({
                        title: `Connecting ${wallet.name}...`,
                        html: '<i class="fa-solid fa-spinner fa-spin fa-2x text-primary my-3"></i><p class="font-12 text-muted">Authorizing wallet for payment...</p>',
                        allowOutsideClick: false,
                        showConfirmButton: false,
                    });

                    const res = await connectByPlatform(walletId);
                    if (res) {
                        await executeTransfer(payeeAddress, amountWei, slotNumber, payeeName, payeeMemberId, amountUsdt);
                    }
                } catch (err) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Wallet Connection Failed',
                        text: err.message || 'Could not connect chosen wallet.',
                    });
                }
            };

        } catch (e) {
            console.error('Payment modal error:', e);
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: e.message || 'Failed to initialize payment.',
            });
        }
    }

    /**
     * Execute on-chain transfer to beneficiary
     */
    async function executeTransfer(payeeAddress, amountWei, slotNumber, payeeName, payeeMemberId, amountUsdt) {
        const provider = resolveProvider();
        if (!provider || !window.ethers) {
            toastr.error('Web3 provider or Ethers.js not ready.');
            return;
        }

        try {
            Swal.fire({
                title: 'Approve in Your Wallet',
                html: `
                    <div class="p-3 bg-light rounded-4 text-start mb-3 border">
                        <div class="d-flex justify-content-between font-12 mb-1.5 pb-1 border-bottom">
                            <span class="text-muted">Transfer Amount:</span>
                            <span class="fw-bold text-primary font-14">$${parseFloat(amountUsdt).toFixed(2)} USDT</span>
                        </div>
                        <div class="d-flex justify-content-between font-12 mb-1.5 pb-1 border-bottom">
                            <span class="text-muted">Payee Member ID:</span>
                            <span class="fw-bold font-monospace text-primary">${payeeMemberId}</span>
                        </div>
                        <div class="d-flex justify-content-between font-12">
                            <span class="text-muted">Network:</span>
                            <span class="fw-bold text-dark">BNB Smart Chain (BEP-20)</span>
                        </div>
                    </div>
                    <div class="d-flex align-items-center justify-content-center gap-2 text-primary font-13 fw-semibold">
                        <i class="fa-solid fa-fingerprint fa-bounce fa-lg"></i>
                        <span>Please authorize in your wallet app with Fingerprint / PIN...</span>
                    </div>
                `,
                allowOutsideClick: false,
                showConfirmButton: false,
            });

            const browserProvider = new ethers.BrowserProvider(provider);
            const signer = await browserProvider.getSigner();
            const usdtContract = new ethers.Contract(state.usdtContractAddress, BEP20_ABI, signer);

            // Execute Transfer
            const tx = await usdtContract.transfer(payeeAddress, amountWei);

            // Show confirmation waiting screen
            Swal.fire({
                title: 'Confirming On BSC...',
                html: `
                    <div class="my-3 text-center">
                        <div class="spinner-border text-success" role="status" style="width: 3rem; height: 3rem;"></div>
                    </div>
                    <p class="font-13 text-muted mb-2">Transaction broadcasted successfully!</p>
                    <p class="font-monospace font-11 text-muted text-break p-2 bg-light rounded border">${tx.hash}</p>
                    <small class="text-muted font-11"><i class="fa-solid fa-clock me-1"></i> Waiting for BSC block validation (~3 seconds)...</small>
                `,
                allowOutsideClick: false,
                showConfirmButton: false,
            });

            // Wait for 1 block confirmation
            await tx.wait(1);

            // Submit TxHash to Laravel Backend
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
            const trackRes = await fetch('/member/track-payment', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrfToken
                },
                body: JSON.stringify({
                    slot_number: slotNumber,
                    tx_hash: tx.hash
                })
            });

            const trackData = await trackRes.json();

            if (trackData.status) {
                Swal.fire({
                    icon: 'success',
                    title: 'Account & Slot Activated!',
                    html: `
                        <p class="text-muted font-13">${trackData.message}</p>
                        <div class="p-2.5 bg-success bg-opacity-10 border border-success border-opacity-25 rounded-3 mb-3">
                            <span class="text-success font-12 fw-bold"><i class="fa-solid fa-circle-check me-1"></i> Verified on BNB Smart Chain</span>
                        </div>
                    `,
                    confirmButtonText: 'View Dashboard',
                    confirmButtonColor: '#198754',
                }).then(() => {
                    window.location.reload();
                });
            } else {
                Swal.fire({
                    icon: 'warning',
                    title: 'Payment Broadcasted',
                    text: trackData.message || 'Payment broadcasted on BSC. Please click Track Payment if activation takes a moment.',
                    confirmButtonText: 'Refresh Page'
                }).then(() => {
                    window.location.reload();
                });
            }

        } catch (err) {
            console.error('Payment execution error:', err);
            if (err.code === 'ACTION_REJECTED' || err.code === 4001) {
                Swal.fire({
                    icon: 'info',
                    title: 'Payment Cancelled',
                    text: 'You declined the transfer in your wallet app.',
                });
            } else {
                Swal.fire({
                    icon: 'error',
                    title: 'Payment Failed',
                    text: err.reason || err.message || 'Could not complete transfer.',
                });
            }
        }
    }

    /**
     * Auto-listen for account/network changes
     */
    if (typeof window !== 'undefined') {
        const injected = resolveProvider();
        if (injected) {
            injected.on?.('accountsChanged', (accounts) => {
                if (accounts.length > 0) {
                    state.connectedAddress = accounts[0].toLowerCase();
                } else {
                    state.connectedAddress = null;
                }
            });
            injected.on?.('chainChanged', () => {
                window.location.reload();
            });
        }
    }

    return {
        SUPPORTED_WALLETS,
        getState: () => state,
        resolveProvider,
        getDetectedWalletId,
        isMobileDevice,
        connectByPlatform,
        showWalletModal,
        onWalletCardClicked,
        openPaymentModal,
        executeTransfer,
        paySlotWithSafePal: (slotNum) => openPaymentModal(slotNum),
        connectWallet: async (autoBind = true) => {
            const provider = resolveProvider();
            if (!provider) {
                showWalletModal({
                    title: 'Connect Web3 Wallet',
                    subtitle: 'Choose your wallet to link with NEVORA',
                    onSelect: async (walletId, wallet) => {
                        const res = await connectByPlatform(walletId);
                        if (res && res.address && autoBind) {
                            const input = document.getElementById('wallet_address_input') || document.querySelector('.web3-wallet-input');
                            if (input) {
                                input.value = res.address;
                                if (typeof toastr !== 'undefined') toastr.success(`${wallet.name} address populated!`);
                            }
                        }
                    }
                });
                return;
            }
            const res = await connectByPlatform();
            if (res && res.address && autoBind) {
                const input = document.getElementById('wallet_address_input') || document.querySelector('.web3-wallet-input');
                if (input) {
                    input.value = res.address;
                    if (typeof toastr !== 'undefined') toastr.success('Wallet address populated!');
                }
            }
            return res;
        }
    };
})();

window.NevoraWeb3 = NevoraWeb3;
