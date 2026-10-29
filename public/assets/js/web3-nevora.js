/**
 * NEVORA Web3 & Multi-Platform Integration Engine (B TITAN Native Experience)
 * Genuine WalletConnect v2 & Injected Web3 Provider Handshake
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

    // Reown / WalletConnect Project ID & Core BSC Configuration
    const REOWN_PROJECT_ID = '392a8f68970ccad5608b6d43cc04cc8c';

    const state = {
        chainId: 56, // BSC Mainnet
        chainIdHex: '0x38',
        chainName: 'BNB Smart Chain',
        rpcUrl: 'https://bsc-dataseed.binance.org',
        blockExplorer: 'https://bscscan.com',
        usdtContractAddress: '0x55d398326f99059fF775485246999027B3197955',
        connectedAddress: null,
        connectedPlatform: null,
        connectedPlatformId: null,
        provider: null,
        signer: null,
        activeWCProvider: null,
        isWalletConnect: false,
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
        },
        {
            id: 'walletconnect',
            name: 'WalletConnect / Other',
            tagline: 'Universal Protocol & QR Code',
            badge: 'Universal',
            badgeClass: 'bg-light text-dark border',
            accentColor: '#3b99fc',
            iconSvg: `<svg width="28" height="28" viewBox="0 0 40 40" fill="none" xmlns="http://www.w3.org/2000/svg"><rect width="40" height="40" rx="10" fill="#3B99FC"/><path d="M14 18C17.3 14.7 22.7 14.7 26 18L26.8 18.8C27 19 27 19.3 26.8 19.5L25.3 21C25.2 21.1 25 21.1 24.9 21L23.8 19.9C21.7 17.8 18.3 17.8 16.2 19.9L15.1 21C15 21.1 14.8 21.1 14.7 21L13.2 19.5C13 19.3 13 19 13.2 18.8L14 18Z" fill="white"/><path d="M28.4 20.4L29.9 21.9C30.1 22.1 30.1 22.4 29.9 22.6L23.9 28.6C23.7 28.8 23.4 28.8 23.2 28.6L19.9 25.3C19.8 25.2 19.6 25.2 19.5 25.3L16.2 28.6C16 28.8 15.7 28.8 15.5 28.6L9.5 22.6C9.3 22.4 9.3 22.1 9.5 21.9L11 20.4C11.2 20.2 11.5 20.2 11.7 20.4L15 23.7C15.1 23.8 15.3 23.8 15.4 23.7L18.7 20.4C18.9 20.2 19.2 20.2 19.4 20.4L22.7 23.7C22.8 23.8 23 23.8 23.1 23.7L26.4 20.4C26.6 20.2 26.9 20.2 27.1 20.4L28.4 20.4Z" fill="white"/></svg>`,
            getProvider: () => null, // Forces WalletConnect v2
        }
    ];

    function isMobileDevice() {
        return /Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent);
    }

    /**
     * Map platform id to native WalletConnect URI deep link scheme
     */
    function getWalletConnectScheme(walletId, uri) {
        const encodedUri = encodeURIComponent(uri);
        switch (walletId) {
            case 'safepal':
                return `safepal://wc?uri=${encodedUri}`;
            case 'trust':
                return `trust://wc?uri=${encodedUri}`;
            case 'metamask':
                return `metamask://wc?uri=${encodedUri}`;
            case 'tokenpocket':
                return `tpdapp://wc?uri=${encodedUri}`;
            case 'bitget':
                return `bitkeep://wc?uri=${encodedUri}`;
            case 'okx':
                return `okex://main/wc?uri=${encodedUri}`;
            case 'coinbase':
                return `cbwallet://wc?uri=${encodedUri}`;
            case 'binance':
                return `bnc://app.binance.com/wc?uri=${encodedUri}`;
            default:
                return `wc:${uri}`;
        }
    }

    /**
     * Map platform id to native App foreground scheme (for post-connection payment authorization)
     */
    function getWalletAppScheme(walletId) {
        switch (walletId) {
            case 'safepal':
                return 'safepal://';
            case 'trust':
                return 'trust://';
            case 'metamask':
                return 'metamask://';
            case 'tokenpocket':
                return 'tpdapp://';
            case 'bitget':
                return 'bitkeep://';
            case 'okx':
                return 'okex://';
            case 'coinbase':
                return 'cbwallet://';
            case 'binance':
                return 'bnc://app.binance.com/';
            default:
                return null;
        }
    }

    /**
     * Detect which wallet platform is actively injected in the current browser window
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
     * Resolve injected provider with graceful fallback
     */
    function resolveProvider(walletId = null) {
        if (walletId) {
            const wallet = SUPPORTED_WALLETS.find(w => w.id === walletId);
            if (wallet && wallet.getProvider) {
                const prov = wallet.getProvider();
                if (prov) return prov;
            }
        }

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
            console.warn('Network switch check warning:', error);
        }
    }

    // WalletConnect EthereumProvider Singleton Instance
    let wcProviderPromise = null;

    async function getWalletConnectProvider() {
        if (wcProviderPromise) {
            return await wcProviderPromise;
        }

        const EthereumProviderClass = window.WalletConnectEthereumProvider || window.NevoraWCBundle?.EthereumProvider;
        if (!EthereumProviderClass) {
            throw new Error('WalletConnect provider library is not loaded. Please refresh the page.');
        }

        wcProviderPromise = EthereumProviderClass.init({
            projectId: REOWN_PROJECT_ID,
            chains: [state.chainId],
            optionalChains: [state.chainId],
            showQrModal: false, // We control high-performance native deep links & QR
            rpcMap: {
                [state.chainId]: state.rpcUrl
            },
            metadata: {
                name: 'NEVORA',
                description: 'NEVORA 3-Matrix Decentralized Network',
                url: window.location.origin,
                icons: [`${window.location.origin}/template/hope-ui/images/favicon.ico`]
            }
        });

        return await wcProviderPromise;
    }

    /**
     * Show QR Code Modal for desktop or fallback scan
     */
    async function showQRCodeModal(uri, walletName = 'Web3 Wallet') {
        let qrDataUrl = '';
        const qrLib = window.NevoraQRCode || window.QRCode;
        if (qrLib && qrLib.toDataURL) {
            try {
                qrDataUrl = await qrLib.toDataURL(uri, { width: 260, margin: 1 });
            } catch (e) {
                console.warn('QR code render error:', e);
            }
        }

        Swal.fire({
            title: `<span class="fw-bold font-16">Scan with ${walletName}</span>`,
            html: `
                <div class="text-center p-2">
                    ${qrDataUrl ? `<img src="${qrDataUrl}" alt="Scan QR" class="img-fluid rounded-3 border p-2 mb-3 bg-white shadow-sm" style="max-width: 230px;" />` : ''}
                    <p class="font-12 text-muted mb-2">Scan this QR code with <strong>${walletName}</strong> to link instantly.</p>
                    <div class="input-group input-group-sm mb-2">
                        <input type="text" class="form-control font-monospace font-10" id="wc-uri-input" value="${uri}" readonly>
                        <button class="btn btn-outline-primary" type="button" onclick="navigator.clipboard.writeText('${uri}').then(() => { if (typeof toastr !== 'undefined') toastr.success('Copied URI!'); })">
                            <i class="fa-regular fa-copy"></i>
                        </button>
                    </div>
                    <small class="text-muted font-10"><i class="fa-solid fa-shield-halved text-success me-1"></i> End-to-end encrypted session handshake</small>
                </div>
            `,
            showConfirmButton: false,
            showCloseButton: true,
            customClass: {
                popup: 'rounded-4 p-3 shadow-lg'
            }
        });
    }

    /**
     * Connect via genuine WalletConnect v2 protocol
     */
    async function connectViaWalletConnect(walletId = 'safepal') {
        const wallet = SUPPORTED_WALLETS.find(w => w.id === walletId) || SUPPORTED_WALLETS[0];
        const wc = await getWalletConnectProvider();

        // If existing session is already active
        if (wc.session && wc.accounts && wc.accounts.length > 0) {
            const address = wc.accounts[0].toLowerCase();
            state.connectedAddress = address;
            state.connectedPlatform = wallet.name;
            state.connectedPlatformId = wallet.id;
            state.activeWCProvider = wc;
            state.isWalletConnect = true;

            if (window.ethers) {
                state.provider = new ethers.BrowserProvider(wc);
                state.signer = await state.provider.getSigner();
            }

            localStorage.setItem('nevora_connected_platform', wallet.name);
            localStorage.setItem('nevora_connected_platform_id', wallet.id);
            localStorage.setItem('nevora_connected_address', address);

            return { address, wallet, provider: wc, isWalletConnect: true };
        }

        // Listener for display_uri to launch mobile app or show QR
        const uriHandler = (uri) => {
            console.log('WalletConnect session URI generated for', wallet.name);
            const isMobile = isMobileDevice();

            if (isMobile) {
                const deepLink = getWalletConnectScheme(wallet.id, uri);
                
                // Immediately trigger native wallet app deep link
                window.location.href = deepLink;

                // Display prompt in Chrome while user approves in wallet
                Swal.fire({
                    title: `Connecting to ${wallet.name}...`,
                    html: `
                        <div class="my-3 text-center">
                            <div class="spinner-border text-primary" role="status" style="width: 2.8rem; height: 2.8rem;"></div>
                        </div>
                        <p class="font-13 text-muted mb-2">We opened <strong>${wallet.name}</strong> on your phone.</p>
                        <p class="font-12 text-muted mb-3">Please unlock your app and tap <strong>Connect</strong>.</p>
                        <div class="d-flex flex-column gap-2">
                            <a href="${deepLink}" class="btn btn-primary btn-sm rounded-pill py-2 fw-semibold shadow-sm">
                                <i class="fa-solid fa-arrow-up-right-from-square me-1"></i> Re-open ${wallet.name}
                            </a>
                            <button type="button" class="btn btn-light btn-sm rounded-pill py-1.5 text-muted border" id="btn-show-wc-qr">
                                <i class="fa-solid fa-qrcode me-1"></i> Show QR Code
                            </button>
                        </div>
                    `,
                    showConfirmButton: false,
                    showCancelButton: true,
                    cancelButtonText: 'Cancel',
                    allowOutsideClick: false,
                    customClass: {
                        popup: 'rounded-4 p-3 shadow-lg'
                    },
                    didOpen: () => {
                        const qrBtn = document.getElementById('btn-show-wc-qr');
                        if (qrBtn) {
                            qrBtn.addEventListener('click', () => {
                                showQRCodeModal(uri, wallet.name);
                            });
                        }
                    }
                });
            } else {
                // Desktop: Show QR code modal
                showQRCodeModal(uri, wallet.name);
            }
        };

        wc.on('display_uri', uriHandler);

        try {
            // Wait for user to approve session in SafePal / Trust / etc.
            await wc.connect();

            if (!wc.accounts || wc.accounts.length === 0) {
                throw new Error('No accounts authorized in your wallet.');
            }

            const address = wc.accounts[0].toLowerCase();
            state.connectedAddress = address;
            state.connectedPlatform = wallet.name;
            state.connectedPlatformId = wallet.id;
            state.activeWCProvider = wc;
            state.isWalletConnect = true;

            if (window.ethers) {
                state.provider = new ethers.BrowserProvider(wc);
                state.signer = await state.provider.getSigner();
            }

            localStorage.setItem('nevora_connected_platform', wallet.name);
            localStorage.setItem('nevora_connected_platform_id', wallet.id);
            localStorage.setItem('nevora_connected_address', address);

            wc.removeListener('display_uri', uriHandler);
            Swal.close();

            return { address, wallet, provider: wc, isWalletConnect: true };
        } catch (err) {
            wc.removeListener('display_uri', uriHandler);
            throw err;
        }
    }

    /**
     * Connect to wallet directly by platform ID
     * Gracefully routes to Injected Web3 (if inside wallet DApp browser) or WalletConnect v2 (if mobile Chrome/desktop)
     */
    async function connectByPlatform(walletId = null) {
        const detectedId = getDetectedWalletId();
        const effectiveId = walletId || detectedId || 'safepal';
        const wallet = SUPPORTED_WALLETS.find(w => w.id === effectiveId) || SUPPORTED_WALLETS[0];
        const injectedProvider = resolveProvider(effectiveId);

        // Scenario A: Injected provider present (e.g. inside SafePal DApp browser or extension)
        if (injectedProvider && effectiveId !== 'walletconnect') {
            const accounts = await injectedProvider.request({ method: 'eth_requestAccounts' });
            if (!accounts || accounts.length === 0) {
                throw new Error('No accounts selected in wallet.');
            }

            const address = accounts[0].toLowerCase();
            state.connectedAddress = address;
            state.connectedPlatform = wallet.name;
            state.connectedPlatformId = wallet.id;
            state.isWalletConnect = false;
            state.activeWCProvider = null;

            await checkAndSwitchNetwork(injectedProvider);

            if (window.ethers) {
                state.provider = new ethers.BrowserProvider(injectedProvider);
                state.signer = await state.provider.getSigner();
            }

            localStorage.setItem('nevora_connected_platform', wallet.name);
            localStorage.setItem('nevora_connected_platform_id', wallet.id);
            localStorage.setItem('nevora_connected_address', address);

            return { address, wallet, provider: injectedProvider, isWalletConnect: false };
        }

        // Scenario B: External Mobile Chrome or Desktop Browser -> Genuine WalletConnect v2
        return await connectViaWalletConnect(effectiveId);
    }

    /**
     * Disconnect active wallet session
     */
    async function disconnectWallet() {
        if (state.activeWCProvider) {
            try {
                await state.activeWCProvider.disconnect();
            } catch (e) {
                console.warn('WalletConnect disconnect warning:', e);
            }
            state.activeWCProvider = null;
        }

        state.connectedAddress = null;
        state.connectedPlatform = null;
        state.connectedPlatformId = null;
        state.provider = null;
        state.signer = null;
        state.isWalletConnect = false;

        localStorage.removeItem('nevora_connected_platform');
        localStorage.removeItem('nevora_connected_platform_id');
        localStorage.removeItem('nevora_connected_address');
    }

    /**
     * Generate HTML for the 9-wallet selector grid (Mobile-Optimized & Responsive)
     */
    function renderWalletGridHtml(actionPrompt = 'Connect to link with NEVORA') {
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
            currentModalCallback(walletId, wallet);
            return;
        }

        // Default behavior: connect & notify
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
            if (res && res.address) {
                Swal.fire({
                    icon: 'success',
                    title: `${wallet.name} Connected!`,
                    html: `<p class="font-mono font-12 text-muted">${res.address}</p>`,
                    timer: 1600,
                    showConfirmButton: false
                });
            }
        } catch (e) {
            if (e.code !== 4001 && e.code !== 'ACTION_REJECTED') {
                Swal.fire({
                    icon: 'error',
                    title: 'Connection Failed',
                    text: e.message || 'Could not connect to wallet.',
                    confirmButtonText: 'Try Again'
                });
            }
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
                    if (err.code !== 4001 && err.code !== 'ACTION_REJECTED') {
                        Swal.fire({
                            icon: 'error',
                            title: 'Wallet Connection Failed',
                            text: err.message || 'Could not connect chosen wallet.',
                        });
                    }
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
     * Execute on-chain transfer to beneficiary (Injected or WalletConnect v2 Signer)
     */
    async function executeTransfer(payeeAddress, amountWei, slotNumber, payeeName, payeeMemberId, amountUsdt) {
        const activeProvider = state.activeWCProvider || resolveProvider();
        if (!activeProvider || !window.ethers) {
            if (typeof toastr !== 'undefined') toastr.error('Web3 provider or Ethers.js not ready.');
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
                        <span>Please authorize in your wallet app with PIN or Fingerprint...</span>
                    </div>
                `,
                allowOutsideClick: false,
                showConfirmButton: false,
            });

            // If mobile device connected via WalletConnect, bring the wallet app to the foreground
            if (isMobileDevice() && state.isWalletConnect) {
                const appScheme = getWalletAppScheme(state.connectedPlatformId);
                if (appScheme) {
                    setTimeout(() => {
                        window.location.href = appScheme;
                    }, 300);
                }
            }

            const browserProvider = new ethers.BrowserProvider(activeProvider);
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
                    title: 'Account & Slot Activated! 🎉',
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
     * Restore previous active session on load
     */
    async function initAutoSession() {
        try {
            // Check injected first
            const injected = resolveProvider();
            if (injected) {
                injected.on?.('accountsChanged', (accounts) => {
                    if (accounts.length > 0) {
                        state.connectedAddress = accounts[0].toLowerCase();
                    } else {
                        disconnectWallet();
                    }
                });
                injected.on?.('chainChanged', () => {
                    window.location.reload();
                });
                return;
            }

            // Check if there was an active WalletConnect session
            const savedAddress = localStorage.getItem('nevora_connected_address');
            const savedPlatform = localStorage.getItem('nevora_connected_platform');
            const savedPlatformId = localStorage.getItem('nevora_connected_platform_id');

            if (savedAddress && savedPlatformId) {
                const wc = await getWalletConnectProvider();
                if (wc.session && wc.accounts && wc.accounts.length > 0) {
                    state.connectedAddress = wc.accounts[0].toLowerCase();
                    state.connectedPlatform = savedPlatform || 'WalletConnect';
                    state.connectedPlatformId = savedPlatformId;
                    state.activeWCProvider = wc;
                    state.isWalletConnect = true;

                    if (window.ethers) {
                        state.provider = new ethers.BrowserProvider(wc);
                        state.signer = await state.provider.getSigner();
                    }
                }
            }
        } catch (e) {
            console.debug('Auto session initialization notice:', e.message);
        }
    }

    if (typeof window !== 'undefined') {
        window.addEventListener('DOMContentLoaded', initAutoSession);
    }

    return {
        SUPPORTED_WALLETS,
        getState: () => state,
        resolveProvider,
        getDetectedWalletId,
        isMobileDevice,
        connectByPlatform,
        connectViaWalletConnect,
        disconnectWallet,
        showWalletModal,
        onWalletCardClicked,
        showQRCodeModal,
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
                                if (typeof toastr !== 'undefined') toastr.success(`${wallet.name} address linked!`);
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
                    if (typeof toastr !== 'undefined') toastr.success('Wallet address linked!');
                }
            }
            return res;
        }
    };
})();

window.NevoraWeb3 = NevoraWeb3;
