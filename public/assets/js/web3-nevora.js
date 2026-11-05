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
            .nevora-wallet-modal-popup .swal2-html-container,
            .nevora-wallet-drawer-popup .swal2-html-container {
                margin: 0 !important;
                padding: 0 !important;
                overflow-x: hidden !important;
            }
            @media (max-width: 767.98px) {
                .nevora-wallet-drawer-popup {
                    border-top-left-radius: 24px !important;
                    border-top-right-radius: 24px !important;
                    border-bottom-left-radius: 0 !important;
                    border-bottom-right-radius: 0 !important;
                    margin: 0 !important;
                    width: 100% !important;
                    max-width: 100% !important;
                    box-shadow: 0 -10px 30px rgba(0, 0, 0, 0.25) !important;
                }
            }
            @media (max-width: 576px) {
                .nevora-wallet-modal-popup, .nevora-wallet-drawer-popup {
                    padding: 14px 10px !important;
                }
                .wallet-option-card {
                    padding: 9px 7px !important;
                }
                .wallet-icon-box {
                    width: 38px !important;
                    height: 38px !important;
                    margin-bottom: 6px !important;
                }
                .wallet-icon-box svg {
                    width: 26px !important;
                    height: 26px !important;
                }
            }
        `;
        document.head.appendChild(style);
    }

    // Reown / WalletConnect Project ID & Core BSC Configuration
    const REOWN_PROJECT_ID = '66e829f3035c9556087997215bf8d997';

    const state = {
        chainId: 56, // BSC Mainnet
        chainIdHex: '0x38',
        chainName: 'BNB Smart Chain',
        rpcUrl: 'https://binance.llamarpc.com',
        fallbackRpcUrls: [
            'https://binance.llamarpc.com',
            'https://bsc-dataseed1.binance.org',
            'https://rpc.ankr.com/bsc',
            'https://bsc-dataseed.binance.org'
        ],
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

    // EIP-6963 Standard Injected Provider Discovery Store
    const eip6963Providers = [];
    if (typeof window !== 'undefined') {
        try {
            window.addEventListener('eip6963:announceProvider', (event) => {
                if (event.detail && event.detail.provider) {
                    const existing = eip6963Providers.find(p => p.info?.uuid === event.detail.info?.uuid);
                    if (!existing) {
                        eip6963Providers.push(event.detail);
                    }
                }
            });
            window.dispatchEvent(new Event('eip6963:requestProvider'));
        } catch (e) {
            console.debug('EIP-6963 init notice:', e);
        }
    }

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
            getProvider: () => {
                if (typeof window === 'undefined') return null;
                if (window.safepalProvider) return window.safepalProvider;
                if (window.safepal) return window.safepal;
                if (window.ethereum?.isSafePal) return window.ethereum;
                if (window.ethereum?.providers?.length) {
                    const found = window.ethereum.providers.find(p => p.isSafePal);
                    if (found) return found;
                }
                const eip = eip6963Providers.find(p => p.info?.name?.toLowerCase().includes('safepal') || p.info?.rdns?.includes('safepal'));
                if (eip) return eip.provider;
                return null;
            },
        },
        {
            id: 'trust',
            name: 'Trust Wallet',
            tagline: 'Binance Multi-Chain Wallet',
            badge: 'Popular',
            badgeClass: 'bg-info text-white',
            accentColor: '#0500ff',
            iconSvg: `<svg width="28" height="28" viewBox="0 0 40 40" fill="none" xmlns="http://www.w3.org/2000/svg"><rect width="40" height="40" rx="10" fill="#0500FF"/><path d="M20 10L28 14V22C28 26.5 24.5 29.8 20 31C15.5 29.8 12 26.5 12 22V14L20 10Z" stroke="white" stroke-width="2.5" stroke-linejoin="round"/></svg>`,
            getProvider: () => {
                if (typeof window === 'undefined') return null;
                if (window.trustwallet) return window.trustwallet;
                if (window.ethereum?.isTrust || window.ethereum?.isTrustWallet) return window.ethereum;
                if (window.ethereum?.providers?.length) {
                    const found = window.ethereum.providers.find(p => p.isTrust || p.isTrustWallet);
                    if (found) return found;
                }
                const eip = eip6963Providers.find(p => p.info?.name?.toLowerCase().includes('trust') || p.info?.rdns?.includes('trust'));
                if (eip) return eip.provider;
                return null;
            },
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
                if (typeof window === 'undefined') return null;
                if (window.ethereum?.isMetaMask && !window.ethereum?.isSafePal && !window.ethereum?.isTrust && !window.ethereum?.isTokenPocket) {
                    return window.ethereum;
                }
                if (window.ethereum?.providers?.length) {
                    const found = window.ethereum.providers.find(p => p.isMetaMask && !p.isSafePal && !p.isTrust && !p.isTokenPocket);
                    if (found) return found;
                }
                const eip = eip6963Providers.find(p => p.info?.name?.toLowerCase().includes('metamask') || p.info?.rdns?.includes('io.metamask'));
                if (eip) return eip.provider;
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
            getProvider: () => {
                if (typeof window === 'undefined') return null;
                if (window.binance) return window.binance;
                if (window.BinanceChain) return window.BinanceChain;
                if (window.ethereum?.isBinance) return window.ethereum;
                if (window.ethereum?.providers?.length) {
                    const found = window.ethereum.providers.find(p => p.isBinance);
                    if (found) return found;
                }
                return null;
            },
        },
        {
            id: 'okx',
            name: 'OKX Wallet',
            tagline: 'Multi-Chain Web3 Portal',
            badge: 'Multi-Chain',
            badgeClass: 'bg-dark text-white',
            accentColor: '#000000',
            iconSvg: `<svg width="28" height="28" viewBox="0 0 40 40" fill="none" xmlns="http://www.w3.org/2000/svg"><rect width="40" height="40" rx="10" fill="#000000"/><rect x="13" y="13" width="6" height="6" fill="white"/><rect x="21" y="13" width="6" height="6" fill="white"/><rect x="17" y="17" width="6" height="6" fill="white"/><rect x="13" y="21" width="6" height="6" fill="white"/><rect x="21" y="21" width="6" height="6" fill="white"/></svg>`,
            getProvider: () => {
                if (typeof window === 'undefined') return null;
                if (window.okxwallet?.ethereum) return window.okxwallet.ethereum;
                if (window.okxwallet) return window.okxwallet;
                if (window.ethereum?.isOkxWallet) return window.ethereum;
                if (window.ethereum?.providers?.length) {
                    const found = window.ethereum.providers.find(p => p.isOkxWallet);
                    if (found) return found;
                }
                return null;
            },
        },
        {
            id: 'bitget',
            name: 'Bitget Wallet',
            tagline: 'Formerly BitKeep Web3',
            badge: 'Trending',
            badgeClass: 'bg-secondary text-white',
            accentColor: '#00d2d3',
            iconSvg: `<svg width="28" height="28" viewBox="0 0 40 40" fill="none" xmlns="http://www.w3.org/2000/svg"><rect width="40" height="40" rx="10" fill="#7530FF"/><path d="M14 16L20 12L26 16V24L20 28L14 24V16Z" fill="#00F0FF"/></svg>`,
            getProvider: () => {
                if (typeof window === 'undefined') return null;
                if (window.bitkeep?.ethereum) return window.bitkeep.ethereum;
                if (window.bitkeep) return window.bitkeep;
                if (window.ethereum?.isBitKeep) return window.ethereum;
                if (window.ethereum?.providers?.length) {
                    const found = window.ethereum.providers.find(p => p.isBitKeep);
                    if (found) return found;
                }
                return null;
            },
        },
        {
            id: 'tokenpocket',
            name: 'TokenPocket',
            tagline: 'DeFi Multi-Chain Native',
            badge: 'DeFi Native',
            badgeClass: 'bg-primary bg-opacity-75 text-white',
            accentColor: '#2980fe',
            iconSvg: `<svg width="28" height="28" viewBox="0 0 40 40" fill="none" xmlns="http://www.w3.org/2000/svg"><rect width="40" height="40" rx="10" fill="#2980FE"/><path d="M14 13H26V18H19V22H25V27H14V13Z" fill="white"/></svg>`,
            getProvider: () => {
                if (typeof window === 'undefined') return null;
                if (window.tokenpocket?.ethereum) return window.tokenpocket.ethereum;
                if (window.tokenpocket) return window.tokenpocket;
                if (window.ethereum?.isTokenPocket) return window.ethereum;
                if (window.ethereum?.providers?.length) {
                    const found = window.ethereum.providers.find(p => p.isTokenPocket);
                    if (found) return found;
                }
                return null;
            },
        },
        {
            id: 'coinbase',
            name: 'Coinbase Wallet',
            tagline: 'Global Web3 Self-Custody',
            badge: 'Global',
            badgeClass: 'bg-primary text-white',
            accentColor: '#0052ff',
            iconSvg: `<svg width="28" height="28" viewBox="0 0 40 40" fill="none" xmlns="http://www.w3.org/2000/svg"><rect width="40" height="40" rx="10" fill="#0052FF"/><rect x="14" y="14" width="12" height="12" rx="3" fill="white"/><rect x="17" y="17" width="6" height="6" rx="1.5" fill="#0052FF"/></svg>`,
            getProvider: () => {
                if (typeof window === 'undefined') return null;
                if (window.coinbaseWalletExtension) return window.coinbaseWalletExtension;
                if (window.ethereum?.isCoinbaseWallet) return window.ethereum;
                if (window.ethereum?.providers?.length) {
                    const found = window.ethereum.providers.find(p => p.isCoinbaseWallet);
                    if (found) return found;
                }
                return null;
            },
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
     * Non-destructive mobile app launcher using custom URI schemes.
     * Triggers the OS app-switch without navigating the current web page away to external websites.
     */
    function launchNativeScheme(schemeUrl) {
        if (!schemeUrl || typeof window === 'undefined') return;
        try {
            const a = document.createElement('a');
            a.href = schemeUrl;
            a.rel = 'noopener noreferrer';
            a.style.display = 'none';
            document.body.appendChild(a);
            a.click();
            setTimeout(() => {
                if (document.body.contains(a)) {
                    document.body.removeChild(a);
                }
            }, 1000);
        } catch (e) {
            console.warn('Native scheme trigger notice:', e);
        }
    }

    /**
     * Map platform id to verified native & universal WalletConnect URI schemes
     * Verified against the official WalletConnect Explorer Registry
     */
    function getWalletDeepLinks(walletId, uri) {
        const encodedUri = encodeURIComponent(uri);
        switch (walletId) {
            case 'safepal':
                return {
                    universal: `https://link.safepal.io/wc?uri=${encodedUri}`,
                    native: `safepalwallet://wc?uri=${encodedUri}`,
                    intent: `intent://wc?uri=${encodedUri}#Intent;scheme=safepalwallet;package=io.safepal.wallet;end`,
                    appScheme: 'safepalwallet://'
                };
            case 'trust':
                return {
                    universal: `https://link.trustwallet.com/wc?uri=${encodedUri}`,
                    native: `trust://wc?uri=${encodedUri}`,
                    intent: `intent://wc?uri=${encodedUri}#Intent;scheme=trust;package=com.wallet.crypto.trustapp;end`,
                    appScheme: 'trust://'
                };
            case 'metamask':
                return {
                    universal: `https://metamask.app.link/wc?uri=${encodedUri}`,
                    native: `metamask://wc?uri=${encodedUri}`,
                    intent: `intent://wc?uri=${encodedUri}#Intent;scheme=metamask;package=io.metamask;end`,
                    appScheme: 'metamask://'
                };
            case 'tokenpocket':
                return {
                    universal: `https://tokenpocket.vip/app?uri=${encodedUri}`,
                    native: `tpoutside://wc?uri=${encodedUri}`,
                    intent: `intent://wc?uri=${encodedUri}#Intent;scheme=tpoutside;package=vip.mytokenpocket;end`,
                    appScheme: 'tpoutside://'
                };
            case 'bitget':
                return {
                    universal: `https://bkapp.vip/wc?uri=${encodedUri}`,
                    native: `bitkeep://wc?uri=${encodedUri}`,
                    intent: `intent://wc?uri=${encodedUri}#Intent;scheme=bitkeep;package=com.bitkeep.wallet;end`,
                    appScheme: 'bitkeep://'
                };
            case 'okx':
                return {
                    universal: `https://www.okx.com/download?appendQuery=true&deeplink=${encodeURIComponent('okx://wallet/walletConnect?uri=' + uri)}`,
                    native: `okex://main/wc?uri=${encodedUri}`,
                    intent: `intent://main/wc?uri=${encodedUri}#Intent;scheme=okex;package=com.okinc.okex.gp;end`,
                    appScheme: 'okex://main'
                };
            case 'binance':
                return {
                    universal: `https://app.binance.com/cedefi/wc?uri=${encodedUri}`,
                    native: `bnc://app.binance.com/cedefi/wc?uri=${encodedUri}`,
                    intent: `intent://app.binance.com/cedefi/wc?uri=${encodedUri}#Intent;scheme=bnc;package=com.binance.dev;end`,
                    appScheme: 'bnc://app.binance.com/'
                };
            case 'coinbase':
                return {
                    universal: `https://go.cb-w.com/wc?uri=${encodedUri}`,
                    native: `cbwallet://wc?uri=${encodedUri}`,
                    intent: `intent://wc?uri=${encodedUri}#Intent;scheme=cbwallet;package=org.toshi;end`,
                    appScheme: 'cbwallet://'
                };
            default:
                return {
                    universal: null,
                    native: `wc:${uri}`,
                    intent: null,
                    appScheme: 'safepalwallet://'
                };
        }
    }

    /**
     * Map platform id to native App foreground scheme (for post-connection payment authorization)
     */
    function getWalletAppScheme(walletId) {
        switch (walletId) {
            case 'safepal': return 'safepalwallet://';
            case 'trust': return 'trust://';
            case 'metamask': return 'metamask://';
            case 'tokenpocket': return 'tpoutside://';
            case 'bitget': return 'bitkeep://';
            case 'okx': return 'okex://main';
            case 'binance': return 'bnc://app.binance.com/';
            case 'coinbase': return 'cbwallet://';
            default: return 'safepalwallet://';
        }
    }

    /**
     * Detect which wallet platform is actively injected in the current browser window
     */
    function getDetectedWalletId() {
        if (typeof window === 'undefined') return null;
        for (const w of SUPPORTED_WALLETS) {
            if (w.id === 'walletconnect') continue;
            try {
                if (w.getProvider && w.getProvider()) {
                    return w.id;
                }
            } catch (e) {
                // Ignore provider query check errors
            }
        }
        if (window.ethereum) return 'safepal';
        return null;
    }

    /**
     * Resolve injected provider with graceful fallback
     */
    function resolveProvider(walletId = null) {
        if (typeof window === 'undefined') return null;

        if (walletId && walletId !== 'walletconnect') {
            const wallet = SUPPORTED_WALLETS.find(w => w.id === walletId);
            if (wallet && wallet.getProvider) {
                try {
                    const prov = wallet.getProvider();
                    if (prov) return prov;
                } catch (e) {
                    console.debug('Provider resolve for', walletId, 'notice:', e);
                }
            }
        }

        // Auto-resolve: Scan each wallet in priority order (SafePal is first)
        for (const w of SUPPORTED_WALLETS) {
            if (w.id === 'walletconnect') continue;
            try {
                if (w.getProvider) {
                    const prov = w.getProvider();
                    if (prov) return prov;
                }
            } catch (e) {
                // Ignore query check errors
            }
        }

        // Fallback: window.ethereum
        if (window.ethereum) {
            if (window.ethereum.providers?.length) {
                const sp = window.ethereum.providers.find(p => p.isSafePal);
                if (sp) return sp;
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
                icons: [`${window.location.origin}/assets/images/logo-main.png`],
                redirect: {
                    native: window.location.origin,
                    universal: window.location.origin + '/register'
                }
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
    async function connectViaWalletConnect(walletId = 'safepal', forceFresh = false) {
        const wallet = SUPPORTED_WALLETS.find(w => w.id === walletId) || SUPPORTED_WALLETS[0];
        const wc = await getWalletConnectProvider();

        const isRegistrationPage = typeof document !== 'undefined' && (
            document.getElementById('register-form') !== null ||
            window.location.pathname.includes('/register')
        );

        // Force a fresh pairing session on the registration page, on user platform switch, or if forceFresh is requested
        if (wc.session && (forceFresh || isRegistrationPage || (state.connectedPlatformId && state.connectedPlatformId !== wallet.id))) {
            try {
                await wc.disconnect();
            } catch (dcErr) {
                console.debug('WC session disconnect notice:', dcErr);
            }
        }

        // If existing session is already active AND valid for this platform outside the registration page (e.g. member dashboard)
        if (wc.session && wc.accounts && wc.accounts.length > 0 && !isRegistrationPage) {
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

            updateRegistrationTopBar(address, wallet.name, wallet.iconSvg);

            return { address, wallet, provider: wc, isWalletConnect: true };
        }

        // Listener for display_uri to launch mobile app or show QR
        const uriHandler = (uri) => {
            console.log('WalletConnect session URI generated for', wallet.name, uri);
            const isMobile = isMobileDevice();

            if (isMobile) {
                const links = getWalletDeepLinks(wallet.id, uri);
                // Prefer direct native app scheme on mobile (e.g. safepalwallet://wc?uri=...)
                // to avoid mobile browsers treating universal HTTPS links as website navigation!
                const primaryNativeUrl = links.native || links.universal;

                // Non-destructively trigger the native app
                launchNativeScheme(primaryNativeUrl);

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
                            <a href="${primaryNativeUrl}" class="btn btn-primary btn-sm rounded-pill py-2 fw-semibold shadow-sm d-flex align-items-center justify-content-center gap-1.5" id="btn-open-wallet-primary">
                                <i class="fa-solid fa-arrow-up-right-from-square"></i> Open ${wallet.name} App
                            </a>
                            <button type="button" class="btn btn-light btn-sm rounded-pill py-1.5 text-muted border font-11" id="btn-show-wc-qr">
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

            updateRegistrationTopBar(address, wallet.name, wallet.iconSvg);

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
    async function connectByPlatform(walletId = null, forceFresh = false) {
        const detectedId = getDetectedWalletId();
        const effectiveId = walletId || detectedId || 'safepal';
        const wallet = SUPPORTED_WALLETS.find(w => w.id === effectiveId) || SUPPORTED_WALLETS[0];
        const injectedProvider = resolveProvider(effectiveId);

        // Scenario A: Injected provider present (e.g. inside SafePal DApp browser or extension)
        if (injectedProvider && effectiveId !== 'walletconnect') {
            let accounts = [];
            // Attempt wallet_requestPermissions first to allow account selection / switching
            try {
                const permissions = await injectedProvider.request({
                    method: 'wallet_requestPermissions',
                    params: [{ eth_accounts: {} }]
                });
                const accPermission = permissions?.find(p => p.parentCapability === 'eth_accounts');
                if (accPermission?.caveats?.[0]?.value) {
                    accounts = accPermission.caveats[0].value;
                }
            } catch (permErr) {
                console.debug('wallet_requestPermissions notice (falling back to eth_requestAccounts):', permErr);
            }

            if (!accounts || accounts.length === 0) {
                accounts = await injectedProvider.request({ method: 'eth_requestAccounts' });
            }

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

            updateRegistrationTopBar(address, wallet.name, wallet.iconSvg);

            return { address, wallet, provider: injectedProvider, isWalletConnect: false };
        }

        // Scenario B: External Mobile Chrome or Desktop Browser -> Genuine WalletConnect v2
        return await connectViaWalletConnect(effectiveId, forceFresh);
    }

    /**
     * Disconnect active wallet session
     */
    async function disconnectWallet(clearStorage = true) {
        try {
            if (state.activeWCProvider) {
                await state.activeWCProvider.disconnect();
            } else {
                const wc = await getWalletConnectProvider().catch(() => null);
                if (wc && wc.session) {
                    await wc.disconnect().catch(() => null);
                }
            }
        } catch (e) {
            console.warn('WalletConnect disconnect notice:', e);
        }

        state.activeWCProvider = null;
        state.connectedAddress = null;
        state.connectedPlatform = null;
        state.connectedPlatformId = null;
        state.provider = null;
        state.signer = null;
        state.isWalletConnect = false;

        if (clearStorage) {
            localStorage.removeItem('nevora_connected_platform');
            localStorage.removeItem('nevora_connected_platform_id');
            localStorage.removeItem('nevora_connected_address');
        }

        updateRegistrationTopBar(null);
    }

    /**
     * Update the Registration Page Top Wallet Connect Bar Idle State (When Extension is detected)
     */
    function updateRegistrationIdleBar(walletName = null) {
        const title = document.getElementById('top-wallet-idle-title');
        const subtitle = document.getElementById('top-wallet-idle-subtitle');
        const btnLabel = document.getElementById('top-wallet-btn-label');
        if (title && walletName) {
            title.innerHTML = `${walletName} Extension Detected <span class="badge bg-success bg-opacity-10 text-success font-10 ms-1 fw-semibold">Ready</span>`;
        }
        if (subtitle && walletName) {
            subtitle.textContent = `Click Connect to link your active ${walletName} address`;
        }
        if (btnLabel && walletName) {
            btnLabel.textContent = `Connect ${walletName}`;
        }
    }

    /**
     * Update the Registration Page Top Wallet Connect Bar (B-TITAN Authentic Flow)
     */
    function updateRegistrationTopBar(address, platformName = null, iconSvg = null) {
        const idleBox = document.getElementById('wallet-connect-idle');
        const activeBox = document.getElementById('wallet-connect-active');
        const hiddenInput = document.getElementById('wallet_address');
        const displayAddr = document.getElementById('top-wallet-address-display');
        const platformBadge = document.getElementById('top-wallet-platform-badge');
        const iconBox = document.getElementById('top-wallet-icon-box');

        if (!address) {
            if (idleBox) idleBox.classList.remove('d-none');
            if (activeBox) activeBox.classList.add('d-none');
            if (hiddenInput) hiddenInput.value = '';
            const title = document.getElementById('top-wallet-idle-title');
            const subtitle = document.getElementById('top-wallet-idle-subtitle');
            const btnLabel = document.getElementById('top-wallet-btn-label');
            if (title) title.textContent = 'No Wallet Connected';
            if (subtitle) subtitle.textContent = 'Click connect to link your SafePal or Web3 wallet';
            if (btnLabel) btnLabel.textContent = 'Connect Wallet';
            return;
        }

        const normalized = address.toLowerCase();
        if (hiddenInput) hiddenInput.value = normalized;
        if (displayAddr) {
            displayAddr.textContent = normalized.slice(0, 6) + '...' + normalized.slice(-4);
        }
        if (platformBadge) {
            platformBadge.textContent = `${platformName || state.connectedPlatform || 'SafePal'} Linked`;
        }
        if (iconBox && iconSvg) {
            iconBox.innerHTML = iconSvg;
        }
        if (idleBox) idleBox.classList.add('d-none');
        if (activeBox) activeBox.classList.remove('d-none');
    }

    /**
     * Generate HTML for the 9-wallet selector grid (Mobile-Optimized & Responsive CSS Grid, Zero Horizontal Scroll)
     */
    function renderWalletGridHtml(actionPrompt = 'Select your wallet app to link with NEVORA') {
        let cardsHtml = '';

        SUPPORTED_WALLETS.forEach(w => {
            cardsHtml += `
                <div class="wallet-option-card p-2.5 p-sm-3 rounded-4 text-center cursor-pointer position-relative"
                     onclick="NevoraWeb3.onWalletCardClicked('${w.id}')"
                     data-wallet-id="${w.id}"
                     style="background: #1e293b; border: 1px solid rgba(56, 189, 248, 0.22); cursor: pointer; transition: all 0.2s ease; box-sizing: border-box; width: 100%;">
                    <div class="d-flex justify-content-between align-items-center mb-1 mb-sm-2">
                        <span class="badge ${w.badgeClass} font-9 font-sm-10 px-1.5 px-sm-2 py-0.5 rounded-pill">${w.badge}</span>
                        <i class="fa-solid fa-chevron-right font-9 font-sm-10 text-muted opacity-50"></i>
                    </div>
                    <div class="wallet-icon-box mb-1.5 mb-sm-2 d-flex align-items-center justify-content-center mx-auto" style="width: 42px; height: 42px;">
                        ${w.iconSvg}
                    </div>
                    <h6 class="fw-bold mb-0 text-white font-13 font-sm-14 text-truncate">${w.name}</h6>
                    <small class="text-muted font-10 font-sm-11 d-block mt-0.5 text-truncate">${w.tagline}</small>
                </div>
            `;
        });

        return `
            <div class="px-1 py-1" style="overflow-x: hidden; width: 100%; box-sizing: border-box;">
                <p class="text-muted font-12 mb-3 text-center">${actionPrompt}</p>
                <div class="nevora-wallet-grid" style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 10px; width: 100%; box-sizing: border-box; overflow-x: hidden;">
                    ${cardsHtml}
                </div>
                <div class="mt-3 text-center">
                    <small class="text-muted font-11">
                        <i class="fa-solid fa-shield-halved text-cyan me-1"></i>
                        Encrypted BSC BEP-20 Web3 Connection. No private keys are ever shared.
                    </small>
                </div>
            </div>
        `;
    }

    // Modal Callback Holder
    let currentModalCallback = null;

    /**
     * Show the All-Platforms B TITAN Wallet Selector Modal / Mobile Bottom Drawer
     */
    function showWalletModal(options = {}) {
        const title = options.title || 'Connect Web3 Wallet';
        const subtitle = options.subtitle || 'Select your wallet app to link with NEVORA';
        currentModalCallback = options.onSelect;
        const isMobile = window.innerWidth < 768;

        Swal.fire({
            title: `
                <div class="w-100 text-center">
                    ${isMobile ? '<div class="mx-auto mb-3" style="width: 44px; height: 5px; border-radius: 10px; background: rgba(255, 255, 255, 0.25);"></div>' : ''}
                    <div class="d-flex align-items-center justify-content-between px-1">
                        <h5 class="fw-bold text-white mb-0 font-16 font-sm-18">${title}</h5>
                    </div>
                </div>
            `,
            html: renderWalletGridHtml(subtitle),
            showConfirmButton: false,
            showCloseButton: true,
            position: isMobile ? 'bottom' : 'center',
            width: isMobile ? '100%' : '560px',
            background: '#151d30',
            color: '#ffffff',
            customClass: {
                popup: isMobile
                    ? 'nevora-wallet-drawer-popup'
                    : 'rounded-4 p-3.5 p-sm-4 shadow-lg border nevora-wallet-modal-popup',
                htmlContainer: 'm-0 p-0 overflow-hidden',
            }
        });
    }

    /**
     * Handle Card Click from Modal
     */
    async function onWalletCardClicked(walletId) {
        const wallet = SUPPORTED_WALLETS.find(w => w.id === walletId) || SUPPORTED_WALLETS[0];

        // Visual feedback on card
        const card = document.querySelector(`[data-wallet-id="${walletId}"]`);
        if (card) {
            card.style.borderColor = '#2563eb';
            card.style.backgroundColor = 'rgba(37, 99, 235, 0.08)';
        }

        if (currentModalCallback) {
            currentModalCallback(walletId, wallet);
            return;
        }

        // Default behavior: connect & notify
        Swal.fire({
            title: `Connecting to ${wallet.name}...`,
            html: `
                <div class="my-3 text-center">
                    <div class="spinner-border text-primary" role="status" style="width: 2.8rem; height: 2.8rem;"></div>
                </div>
                <p class="font-13 text-muted mb-2">Please approve the connection in <strong>${wallet.name}</strong>.</p>
                <small class="text-muted font-11"><i class="fa-solid fa-fingerprint me-1"></i> Authorize address link on BNB Smart Chain</small>
            `,
            allowOutsideClick: false,
            showConfirmButton: false,
            showCancelButton: true,
            cancelButtonText: 'Cancel',
        });

        try {
            const res = await connectByPlatform(walletId);
            if (res && res.address) {
                Swal.close();
                updateRegistrationTopBar(res.address, wallet.name, wallet.iconSvg);
                if (typeof toastr !== 'undefined') {
                    toastr.success(`${wallet.name} connected successfully!`);
                }
            }
        } catch (e) {
            if (e.code === 4001 || e.code === 'ACTION_REJECTED' || e.message?.includes('rejected') || e.message?.includes('denied')) {
                Swal.fire({
                    icon: 'info',
                    title: 'Connection Cancelled',
                    text: `You cancelled the connection request in ${wallet.name}.`,
                    confirmButtonText: 'OK'
                });
            } else {
                Swal.fire({
                    icon: 'error',
                    title: 'Connection Failed',
                    text: e.message || `Could not connect to ${wallet.name}.`,
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
     * Fetch user's BEP-20 USDT balance on BSC Mainnet with redundant fast RPC fallback
     */
    async function getUsdtBalance(address) {
        if (!address) return 0;
        const endpoints = state.fallbackRpcUrls || [state.rpcUrl];
        for (const rpc of endpoints) {
            try {
                const bscProvider = new ethers.JsonRpcProvider(rpc);
                const usdtContract = new ethers.Contract(state.usdtContractAddress, BEP20_ABI, bscProvider);
                const rawBalance = await usdtContract.balanceOf(address);
                const formatted = parseFloat(ethers.formatUnits(rawBalance, 18));
                if (!isNaN(formatted)) return formatted;
            } catch (e) {
                console.debug(`USDT balance query failed on ${rpc}, trying next fallback:`, e?.message || e);
            }
        }
        return 0;
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
            // Check USDT Balance First
            const userAddress = state.connectedAddress || (await (new ethers.BrowserProvider(activeProvider)).getSigner()).address;
            const usdtBalance = await getUsdtBalance(userAddress);

            if (usdtBalance < parseFloat(amountUsdt)) {
                Swal.fire({
                    icon: 'error',
                    title: 'Insufficient Balance',
                    html: `
                        <div class="p-3 bg-light rounded-4 text-start mb-3 border">
                            <div class="d-flex justify-content-between font-13 mb-1.5 pb-1 border-bottom">
                                <span class="text-muted">Your Wallet Balance:</span>
                                <span class="fw-bold text-danger font-monospace">$${usdtBalance.toFixed(2)} USDT</span>
                            </div>
                            <div class="d-flex justify-content-between font-13">
                                <span class="text-muted">Required Amount:</span>
                                <span class="fw-bold text-primary font-monospace">$${parseFloat(amountUsdt).toFixed(2)} USDT</span>
                            </div>
                        </div>
                        <p class="font-12 text-muted mb-0">Your balance is not sufficient. Please deposit at least $${parseFloat(amountUsdt).toFixed(2)} USDT into your wallet on BNB Smart Chain and try again.</p>
                    `,
                    confirmButtonText: 'OK',
                    confirmButtonColor: '#2563eb'
                });
                return;
            }

            const appScheme = getWalletAppScheme(state.connectedPlatformId || 'safepal');
            const walletName = state.connectedPlatform || 'Wallet';

            Swal.fire({
                title: 'Approve in Your Wallet',
                html: `
                    <div class="p-3 bg-light rounded-4 text-start mb-3 border">
                        <div class="d-flex justify-content-between font-12 mb-1.5 pb-1 border-bottom">
                            <span class="text-muted">Transfer Amount:</span>
                            <span class="fw-bold text-success font-14">$${parseFloat(amountUsdt).toFixed(2)} USDT</span>
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
                    <div class="d-flex align-items-center justify-content-center gap-2 text-primary font-13 fw-semibold mb-3">
                        <i class="fa-solid fa-fingerprint fa-bounce fa-lg"></i>
                        <span>Please authorize in ${walletName} with PIN or Fingerprint...</span>
                    </div>

                    ${isMobileDevice() && state.isWalletConnect ? `
                        <a href="${appScheme}" class="btn btn-primary rounded-pill py-2.5 w-100 fw-bold shadow-sm d-flex align-items-center justify-content-center gap-2 text-decoration-none mb-2" id="btn-open-wallet-pay">
                            <i class="fa-solid fa-arrow-up-right-from-square"></i> Open ${walletName} App to Authorize
                        </a>
                    ` : ''}
                `,
                allowOutsideClick: false,
                showConfirmButton: false,
                showCancelButton: true,
                cancelButtonText: 'Cancel'
            });

            const browserProvider = new ethers.BrowserProvider(activeProvider);
            const signer = await browserProvider.getSigner();
            const usdtContract = new ethers.Contract(state.usdtContractAddress, BEP20_ABI, signer);

            // Pre-configured gasLimit avoids slow estimateGas round-trips over background throttled provider
            const txOverrides = {
                gasLimit: 100000n
            };

            // Non-destructively prompt the native app to open on mobile
            if (isMobileDevice() && state.isWalletConnect && appScheme) {
                setTimeout(() => {
                    launchNativeScheme(appScheme);
                }, 200);
            }

            // Execute Transfer
            const tx = await usdtContract.transfer(payeeAddress, amountWei, txOverrides);

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

    // Concurrency lock to prevent simultaneous duplicate /register calls
    let isRegistrationActive = false;

    /**
     * Atomic 1-Step Registration Flow:
     * 1. Connect wallet (SafePal / etc.)
     * 2. Pre-check USDT balance (>= 15.00 USDT)
     * 3. Fetch payee preview for sponsor
     * 4. Prompt user to authorize 15 USDT transfer in wallet
     * 5. Catch tx.hash on BSC
     * 6. POST /register with tx_hash -> User created with is_paid = true & placed in tree
     */
    async function executePayToRegister(formInputs, walletId = null) {
        if (isRegistrationActive) return;
        isRegistrationActive = true;
        try {
            let connectedAddress = state.connectedAddress;
            let wallet = SUPPORTED_WALLETS.find(w => w.id === state.connectedPlatformId);

            if (!connectedAddress) {
                const detectedId = getDetectedWalletId();
                const chosenId = walletId || detectedId || 'safepal';
                wallet = SUPPORTED_WALLETS.find(w => w.id === chosenId) || SUPPORTED_WALLETS[0];

                Swal.fire({
                    title: `Connecting to ${wallet.name}...`,
                    html: '<i class="fa-solid fa-spinner fa-spin fa-2x text-primary my-3"></i><p class="font-12 text-muted">Please approve the connection in your wallet app.</p>',
                    allowOutsideClick: false,
                    showConfirmButton: false,
                    showCancelButton: true,
                    cancelButtonText: 'Cancel',
                });

                const connRes = await connectByPlatform(chosenId);
                if (!connRes || !connRes.address) {
                    return;
                }

                connectedAddress = connRes.address.toLowerCase();
                wallet = connRes.wallet;
            } else if (!wallet) {
                wallet = SUPPORTED_WALLETS[0];
            }

            // Sync hidden wallet address and top display
            const walletHidden = document.getElementById('wallet_address');
            if (walletHidden) walletHidden.value = connectedAddress;
            updateRegistrationTopBar(connectedAddress, wallet.name, wallet.iconSvg);

            // Check USDT Balance on BSC
            Swal.fire({
                title: 'Checking USDT Balance...',
                html: '<div class="spinner-border text-primary my-3" role="status"></div><p class="font-12 text-muted">Verifying wallet balance on BNB Smart Chain...</p>',
                allowOutsideClick: false,
                showConfirmButton: false,
            });

            const usdtBalance = await getUsdtBalance(connectedAddress);

            // Strict check: if less than 15.00 USDT, BLOCK REGISTRATION
            if (usdtBalance < 15.0) {
                Swal.fire({
                    icon: 'error',
                    title: 'Insufficient Balance',
                    html: `
                        <div class="p-3 bg-light rounded-4 text-start mb-3 border">
                            <div class="d-flex justify-content-between font-13 mb-1.5 pb-1 border-bottom">
                                <span class="text-muted">Your Wallet Balance:</span>
                                <span class="fw-bold text-danger font-monospace">$${usdtBalance.toFixed(2)} USDT</span>
                            </div>
                            <div class="d-flex justify-content-between font-13">
                                <span class="text-muted">Required to Register:</span>
                                <span class="fw-bold text-primary font-monospace">$15.00 USDT</span>
                            </div>
                        </div>
                        <p class="font-12 text-muted mb-1">Your balance is not sufficient to register.</p>
                        <p class="font-11 text-muted mb-0">Please deposit at least 15.00 USDT into your wallet on BNB Smart Chain and try again.</p>
                        <div class="mt-2 text-danger font-11 fw-semibold">
                            <i class="fa-solid fa-circle-exclamation me-1"></i> Your account has NOT been registered.
                        </div>
                    `,
                    confirmButtonText: 'OK',
                    confirmButtonColor: '#2563eb'
                });
                return;
            }

            // Resolve matrix payee for this registration
            Swal.fire({
                title: 'Resolving Payee Route...',
                html: '<i class="fa-solid fa-spinner fa-spin fa-2x text-primary my-3"></i><p class="font-12 text-muted">Calculating 3-matrix eligible beneficiary...</p>',
                allowOutsideClick: false,
                showConfirmButton: false,
            });

            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
            const payeeRes = await fetch('/register-payee-preview', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrfToken
                },
                body: JSON.stringify({
                    sponsor_id: formInputs.sponsor_id
                })
            });

            const payeeData = await payeeRes.json();
            if (!payeeData || !payeeData.status) {
                throw new Error(payeeData?.message || 'Could not resolve payment payee route.');
            }

            const payeeAddress = payeeData.payee_address;
            const payeeMemberId = payeeData.payee_member_id;
            const payeeMasked = payeeData.payee_masked_address || (payeeAddress.slice(0, 6) + '...' + payeeAddress.slice(-4));
            const amountUsdt = payeeData.amount || 15.0;
            const amountWei = BigInt(payeeData.amount_wei || '15000000000000000000');

            const appScheme = getWalletAppScheme(wallet.id);

            // Stash registration details safely in localStorage before triggering transfer
            const pendingRegistration = {
                ...formInputs,
                wallet_address: connectedAddress,
                amount: amountUsdt,
                payee_address: payeeAddress,
                timestamp: Date.now()
            };
            localStorage.setItem('nevora_pending_registration', JSON.stringify(pendingRegistration));

            Swal.fire({
                title: 'Authorize $15 USDT in Wallet',
                html: `
                    <div class="p-3 bg-light rounded-4 text-start mb-3 border">
                        <div class="d-flex justify-content-between font-12 mb-1.5 pb-1 border-bottom">
                            <span class="text-muted">Registration Fee:</span>
                            <span class="fw-bold text-success font-14">$${parseFloat(amountUsdt).toFixed(2)} USDT</span>
                        </div>
                        <div class="d-flex justify-content-between font-12 mb-1.5 pb-1 border-bottom">
                            <span class="text-muted">Payee Member ID:</span>
                            <span class="fw-bold font-monospace text-primary">${payeeMemberId}</span>
                        </div>
                        <div class="d-flex justify-content-between font-12 mb-1.5 pb-1 border-bottom">
                            <span class="text-muted">Payee Wallet:</span>
                            <span class="fw-bold font-monospace text-dark">${payeeMasked}</span>
                        </div>
                        <div class="d-flex justify-content-between font-12">
                            <span class="text-muted">Network:</span>
                            <span class="fw-bold text-dark">BNB Smart Chain (BEP-20)</span>
                        </div>
                    </div>

                    <div class="d-flex align-items-center justify-content-center gap-2 text-primary font-13 fw-semibold mb-3">
                        <i class="fa-solid fa-fingerprint fa-bounce fa-lg"></i>
                        <span>Please authorize in ${wallet.name} with PIN or Fingerprint...</span>
                    </div>

                    ${isMobileDevice() && state.isWalletConnect ? `
                        <a href="${appScheme}" class="btn btn-primary rounded-pill py-2.5 w-100 fw-bold shadow-sm d-flex align-items-center justify-content-center gap-2 text-decoration-none mb-2" id="btn-open-wallet-pay">
                            <i class="fa-solid fa-arrow-up-right-from-square"></i> Open ${wallet.name} App to Authorize
                        </a>
                    ` : ''}
                `,
                allowOutsideClick: false,
                showConfirmButton: false,
                showCancelButton: true,
                cancelButtonText: 'Cancel Registration',
            });

            const activeProvider = state.activeWCProvider || resolveProvider();
            const browserProvider = new ethers.BrowserProvider(activeProvider);
            const signer = await browserProvider.getSigner();
            const usdtContract = new ethers.Contract(state.usdtContractAddress, BEP20_ABI, signer);

            // Pre-configured gasLimit avoids slow estimateGas round-trips over background throttled provider
            const txOverrides = {
                gasLimit: 100000n
            };

            // Non-destructively prompt the native app to open on mobile
            if (isMobileDevice() && state.isWalletConnect && appScheme) {
                setTimeout(() => {
                    launchNativeScheme(appScheme);
                }, 200);
            }

            const tx = await usdtContract.transfer(payeeAddress, amountWei, txOverrides);

            // Transaction broadcasted! Immediately attach tx_hash to pending storage
            pendingRegistration.tx_hash = tx.hash;
            localStorage.setItem('nevora_pending_registration', JSON.stringify(pendingRegistration));

            Swal.fire({
                title: 'Validating on BSC...',
                html: `
                    <div class="my-3 text-center">
                        <div class="spinner-border text-success" role="status" style="width: 3rem; height: 3rem;"></div>
                    </div>
                    <p class="font-13 text-muted mb-2">Payment broadcasted successfully!</p>
                    <p class="font-monospace font-11 text-muted text-break p-2 bg-light rounded border">${tx.hash}</p>
                    <small class="text-muted font-11"><i class="fa-solid fa-clock me-1"></i> Confirming block on BSC & activating ID (~3 seconds)...</small>
                `,
                allowOutsideClick: false,
                showConfirmButton: false,
            });

            await tx.wait(1);

            const regPayload = {
                ...formInputs,
                wallet_address: connectedAddress,
                tx_hash: tx.hash
            };

            const regRes = await fetch('/register', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrfToken
                },
                body: JSON.stringify(regPayload)
            });

            const regData = await regRes.json();

            if (regData && regData.status) {
                localStorage.removeItem('nevora_pending_registration');
                if (regData.is_already_logged_in) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Member Registered & Activated! 🎉',
                        html: `
                            <p class="text-muted font-13 mb-3">${regData.message}</p>
                            <div class="p-3 bg-light rounded-4 border text-start mb-3 font-12">
                                <div class="d-flex justify-content-between mb-1.5 pb-1 border-bottom">
                                    <span class="text-muted">New Member ID:</span>
                                    <span class="fw-bold font-monospace text-primary font-14">${regData.member_id}</span>
                                </div>
                                <div class="d-flex justify-content-between mb-1.5 pb-1 border-bottom">
                                    <span class="text-muted">Full Name:</span>
                                    <span class="fw-bold text-dark">${regData.name}</span>
                                </div>
                                <div class="d-flex justify-content-between mb-1.5 pb-1 border-bottom">
                                    <span class="text-muted">Sponsor ID:</span>
                                    <span class="fw-bold font-monospace text-primary">${regData.sponsor_id}</span>
                                </div>
                                <div class="d-flex justify-content-between mb-1.5 pb-1 border-bottom">
                                    <span class="text-muted">Linked Wallet:</span>
                                    <span class="fw-bold font-monospace text-dark">${connectedAddress.slice(0, 6)}...${connectedAddress.slice(-4)}</span>
                                </div>
                                <div class="d-flex justify-content-between">
                                    <span class="text-muted">Status:</span>
                                    <span class="badge bg-success text-white px-2 py-0.5 rounded-pill"><i class="fa-solid fa-circle-check me-1"></i>Slot 1 Paid & Active</span>
                                </div>
                            </div>
                            <div class="p-2.5 bg-primary bg-opacity-10 border border-primary border-opacity-25 rounded-3 mb-2 font-11 text-primary text-start">
                                <i class="fa-solid fa-circle-check me-1"></i> Your current login session (<strong>${regData.current_user_name || ''}</strong>) has been preserved.
                            </div>
                        `,
                        confirmButtonText: 'View My Directs',
                        confirmButtonColor: '#2563eb',
                        showCancelButton: true,
                        cancelButtonText: 'Stay Here',
                        allowOutsideClick: false
                    }).then((result) => {
                        if (result.isConfirmed) {
                            window.location.href = regData.redirect || '/member/network/directs';
                        } else {
                            window.location.reload();
                        }
                    });
                } else {
                    Swal.fire({
                        icon: 'success',
                        title: 'Registration & Activation Successful! 🎉',
                        html: `
                            <p class="text-muted font-13 mb-3">${regData.message}</p>
                            <div class="p-3 bg-light rounded-4 border text-start mb-3 font-12">
                                <div class="d-flex justify-content-between mb-1.5 pb-1 border-bottom">
                                    <span class="text-muted">Your Member ID:</span>
                                    <span class="fw-bold font-monospace text-primary font-14">${regData.member_id}</span>
                                </div>
                                <div class="d-flex justify-content-between mb-1.5 pb-1 border-bottom">
                                    <span class="text-muted">Linked Wallet:</span>
                                    <span class="fw-bold font-monospace text-dark">${connectedAddress.slice(0, 6)}...${connectedAddress.slice(-4)}</span>
                                </div>
                                <div class="d-flex justify-content-between">
                                    <span class="text-muted">Status:</span>
                                    <span class="badge bg-success text-white px-2 py-0.5 rounded-pill"><i class="fa-solid fa-circle-check me-1"></i>Slot 1 Paid & Active</span>
                                </div>
                            </div>
                        `,
                        confirmButtonText: 'Go to Dashboard',
                        confirmButtonColor: '#2563eb',
                        allowOutsideClick: false
                    }).then(() => {
                        window.location.href = regData.redirect || '/member/dashboard';
                    });
                }
            } else {
                throw new Error(regData?.message || 'Failed to complete registration.');
            }

        } catch (err) {
            console.error('Pay to register error:', err);
            if (err.code === 'ACTION_REJECTED' || err.code === 4001) {
                localStorage.removeItem('nevora_pending_registration');
                Swal.fire({
                    icon: 'info',
                    title: 'Payment Cancelled',
                    text: 'You declined the transfer in your wallet app. Your account was NOT registered.',
                    confirmButtonText: 'OK'
                });
            } else {
                Swal.fire({
                    icon: 'error',
                    title: 'Registration Error',
                    text: err.reason || err.message || 'Could not complete registration payment.',
                    confirmButtonText: 'Try Again'
                });
            }
        } finally {
            isRegistrationActive = false;
        }
    }

    /**
     * Safety Net: Auto-detect & complete registration if payment was broadcasted on BSC
     * but the browser tab was closed, refreshed, or interrupted on mobile.
     */
    async function resumePendingRegistration() {
        if (typeof window === 'undefined') return false;
        if (isRegistrationActive) return false;
        const raw = localStorage.getItem('nevora_pending_registration');
        if (!raw) return false;
        isRegistrationActive = true;
        try {
            const pending = JSON.parse(raw);
            // Require valid tx_hash and age < 2 hours
            if (!pending.tx_hash || !pending.tx_hash.startsWith('0x') || (Date.now() - (pending.timestamp || 0) > 2 * 60 * 60 * 1000)) {
                return false;
            }

            Swal.fire({
                title: 'Finalizing Your Registration...',
                html: `
                    <div class="my-3 text-center">
                        <div class="spinner-border text-primary" role="status" style="width: 2.8rem; height: 2.8rem;"></div>
                    </div>
                    <p class="font-13 text-muted mb-2">We detected your completed $15.00 USDT payment on BSC!</p>
                    <p class="font-monospace font-11 text-muted text-break p-2 bg-light rounded border">${pending.tx_hash}</p>
                    <small class="text-muted font-11"><i class="fa-solid fa-shield-halved text-success me-1"></i> Completing account activation...</small>
                `,
                allowOutsideClick: false,
                showConfirmButton: false,
            });

            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
            const regRes = await fetch('/register', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrfToken
                },
                body: JSON.stringify(pending)
            });

            const regData = await regRes.json();
            if (regData && regData.status) {
                localStorage.removeItem('nevora_pending_registration');
                Swal.fire({
                    icon: 'success',
                    title: 'Registration & Activation Successful! 🎉',
                    html: `
                        <p class="text-muted font-13 mb-3">${regData.message || 'Your account is now active.'}</p>
                        <div class="p-3 bg-light rounded-4 border text-start mb-3 font-12">
                            <div class="d-flex justify-content-between mb-1.5 pb-1 border-bottom">
                                <span class="text-muted">Member ID:</span>
                                <span class="fw-bold font-monospace text-primary font-14">${regData.member_id}</span>
                            </div>
                            <div class="d-flex justify-content-between mb-1.5 pb-1 border-bottom">
                                <span class="text-muted">Linked Wallet:</span>
                                <span class="fw-bold font-monospace text-dark">${pending.wallet_address ? (pending.wallet_address.slice(0, 6) + '...' + pending.wallet_address.slice(-4)) : ''}</span>
                            </div>
                        </div>
                    `,
                    confirmButtonText: 'Go to Dashboard',
                    confirmButtonColor: '#2563eb',
                    allowOutsideClick: false
                }).then(() => {
                    window.location.href = regData.redirect || '/member/dashboard';
                });
                return true;
            } else {
                console.warn('Pending registration recovery notice:', regData);
                if (regData?.message?.includes('already been registered') || regData?.message?.includes('already linked')) {
                    localStorage.removeItem('nevora_pending_registration');
                }
                Swal.close();
                return false;
            }
        } catch (e) {
            console.error('Pending registration recovery error:', e);
            return false;
        } finally {
            isRegistrationActive = false;
        }
    }

    /**
     * Listener for account changes from injected wallet
     */
    function handleAccountsChanged(accounts) {
        if (accounts && accounts.length > 0) {
            const address = accounts[0].toLowerCase();
            const detectedId = getDetectedWalletId() || 'safepal';
            const walletObj = SUPPORTED_WALLETS.find(w => w.id === detectedId) || SUPPORTED_WALLETS[0];

            state.connectedAddress = address;
            state.connectedPlatform = walletObj.name;
            state.connectedPlatformId = walletObj.id;
            state.isWalletConnect = false;

            localStorage.setItem('nevora_connected_platform', walletObj.name);
            localStorage.setItem('nevora_connected_platform_id', walletObj.id);
            localStorage.setItem('nevora_connected_address', address);

            updateRegistrationTopBar(address, walletObj.name, walletObj.iconSvg);
        } else {
            disconnectRegistrationWallet();
        }
    }

    /**
     * Disconnect active wallet session specifically on registration page
     */
    async function disconnectRegistrationWallet() {
        await disconnectWallet();
        updateRegistrationTopBar(null);
    }

    /**
     * Restore previous active session on load (Dashboard ONLY - NEVER on Registration)
     */
    async function initAutoSession() {
        try {
            // STRICT RULE: On the registration page, NEVER auto-connect on load!
            // The user must explicitly click "Connect Wallet" and choose from the B-TITAN drawer.
            const isRegistrationPage = typeof document !== 'undefined' && (
                document.getElementById('register-form') !== null ||
                window.location.pathname.includes('/register')
            );

            if (isRegistrationPage) {
                updateRegistrationTopBar(null);
                return;
            }

            // Outside registration (e.g. member dashboard), attach listeners and restore session if present
            const injected = resolveProvider();
            if (injected) {
                const detectedId = getDetectedWalletId() || 'safepal';
                const walletObj = SUPPORTED_WALLETS.find(w => w.id === detectedId) || SUPPORTED_WALLETS[0];

                if (injected.on) {
                    try {
                        injected.removeListener?.('accountsChanged', handleAccountsChanged);
                        injected.on('accountsChanged', handleAccountsChanged);
                        injected.on('chainChanged', () => {
                            window.location.reload();
                        });
                    } catch (evErr) {
                        console.debug('Event listener attach notice:', evErr);
                    }
                }

                // Check if account is ALREADY connected in dashboard
                let accounts = [];
                try {
                    accounts = await injected.request({ method: 'eth_accounts' });
                } catch (e) {
                    console.debug('eth_accounts check notice:', e);
                }

                if (accounts && accounts.length > 0) {
                    const address = accounts[0].toLowerCase();
                    state.connectedAddress = address;
                    state.connectedPlatform = walletObj.name;
                    state.connectedPlatformId = walletObj.id;
                    state.isWalletConnect = false;
                    state.activeWCProvider = null;

                    if (window.ethers) {
                        state.provider = new ethers.BrowserProvider(injected);
                        state.signer = await state.provider.getSigner().catch(() => null);
                    }

                    localStorage.setItem('nevora_connected_platform', walletObj.name);
                    localStorage.setItem('nevora_connected_platform_id', walletObj.id);
                    localStorage.setItem('nevora_connected_address', address);
                }
                return;
            }

            // Check if there was an active WalletConnect session (Dashboard only)
            const savedAddress = localStorage.getItem('nevora_connected_address');
            const savedPlatform = localStorage.getItem('nevora_connected_platform');
            const savedPlatformId = localStorage.getItem('nevora_connected_platform_id');

            if (savedAddress && savedPlatformId) {
                const wc = await getWalletConnectProvider().catch(() => null);
                if (wc && wc.session && wc.accounts && wc.accounts.length > 0) {
                    state.connectedAddress = wc.accounts[0].toLowerCase();
                    state.connectedPlatform = savedPlatform || 'WalletConnect';
                    state.connectedPlatformId = savedPlatformId;
                    state.activeWCProvider = wc;
                    state.isWalletConnect = true;

                    if (window.ethers) {
                        state.provider = new ethers.BrowserProvider(wc);
                        state.signer = await state.provider.getSigner().catch(() => null);
                    }
                }
            }
        } catch (e) {
            console.debug('Auto session initialization notice:', e.message);
        }
    }

    if (typeof window !== 'undefined') {
        const runInit = () => { initAutoSession(); };
        if (document.readyState === 'loading') {
            window.addEventListener('DOMContentLoaded', runInit);
        } else {
            runInit();
        }
        window.addEventListener('ethereum#initialized', runInit, { once: true });
        window.addEventListener('eip6963:announceProvider', runInit);
        // Extension content scripts often inject within 100-800ms
        setTimeout(runInit, 150);
        setTimeout(runInit, 500);
        setTimeout(runInit, 1200);
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
        disconnectRegistrationWallet,
        updateRegistrationIdleBar,
        updateRegistrationTopBar,
        showWalletModal,
        onWalletCardClicked,
        showQRCodeModal,
        openPaymentModal,
        executeTransfer,
        getUsdtBalance,
        executePayToRegister,
        resumePendingRegistration,
        launchNativeScheme,
        getWalletAppScheme,
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
