/**
 * NEVORA Web3 & Multi-Platform Integration Engine (v2.0 Clean Architecture)
 * - Rock-solid BSC BEP-20 USDT Support
 * - Static Network Ethers v6 Integration (No background eth_blockNumber fetch failures)
 * - Multi-Endpoint RPC Failover Pool
 * - Smart In-App DApp Browser & Extension Detection
 * - App-Switch Resilient WalletConnect v2 Lifecycle & Direct HTTPS Tx Polling
 */

const NevoraWeb3 = (function () {
    'use strict';

    // 1. Inject responsive modal & drawer styles
    if (typeof document !== 'undefined') {
        const style = document.createElement('style');
        style.id = 'nevora-web3-styles';
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
            .wallet-option-card {
                cursor: pointer;
                transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
                border: 1.5px solid #e2e8f0;
                background: #ffffff;
            }
            .wallet-option-card:hover {
                border-color: #2563eb !important;
                transform: translateY(-2px);
                box-shadow: 0 8px 20px rgba(37, 99, 235, 0.12);
            }
            [data-bs-theme="dark"] .wallet-option-card {
                background: #1e293b;
                border-color: #334155;
            }
            [data-bs-theme="dark"] .wallet-option-card:hover {
                border-color: #3b82f6 !important;
                background: #243048;
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

    // 2. Constants & Network Configuration
    const REOWN_PROJECT_ID = '392a8f68970ccad5608b6d43cc04cc8c';
    const BSC_CHAIN_ID = 56;
    const BSC_CHAIN_ID_HEX = '0x38';
    const BSC_NAME = 'BNB Smart Chain';
    const USDT_CONTRACT_ADDRESS = '0x55d398326f99059fF775485246999027B3197955';
    const BLOCK_EXPLORER = 'https://bscscan.com';

    // Tier-1 Public BSC RPC Endpoints with round-robin failover
    const BSC_RPC_ENDPOINTS = [
        'https://bsc-dataseed.binance.org',
        'https://bsc-dataseed1.defibit.io',
        'https://bsc-dataseed1.ninicoin.io',
        'https://rpc.ankr.com/bsc',
        'https://binance.llamarpc.com'
    ];

    // Static Ethers Network object locks chainId to 56 to suppress unnecessary eth_blockNumber calls
    const BSC_STATIC_NETWORK = (typeof ethers !== 'undefined' && ethers.Network)
        ? ethers.Network.from({ chainId: BSC_CHAIN_ID, name: 'binance' })
        : null;

    // BEP-20 Standard Interface
    const BEP20_ABI = [
        "function name() view returns (string)",
        "function symbol() view returns (string)",
        "function decimals() view returns (uint8)",
        "function balanceOf(address owner) view returns (uint256)",
        "function transfer(address to, uint256 amount) returns (bool)",
        "event Transfer(address indexed from, address indexed to, uint256 value)"
    ];

    // Engine Internal State
    const state = {
        connectedAddress: null,
        connectedPlatform: null,
        connectedPlatformId: null,
        provider: null,
        signer: null,
        activeWCProvider: null,
        isWalletConnect: false,
    };

    let isRegistrationActive = false;
    let wcProviderPromise = null;

    // 3. EIP-6963 Standard Provider Store
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

    // 4. Supported Wallets Directory
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
            getProvider: () => null,
        }
    ];

    // 5. Environment & Mobile Utilities
    function isMobileDevice() {
        return /Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent);
    }

    function isInAppWalletBrowser() {
        if (typeof window === 'undefined') return false;
        if (!isMobileDevice()) return false;
        return !!(
            window.safepalProvider ||
            window.safepal ||
            window.trustwallet ||
            window.tokenpocket ||
            (window.ethereum && (window.ethereum.isSafePal || window.ethereum.isTrust || window.ethereum.isMetaMask || window.ethereum.isTokenPocket))
        );
    }

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
            }, 800);
        } catch (e) {
            console.debug('Native scheme trigger notice:', e);
        }
    }

    function getWalletDeepLinks(walletId, uri) {
        const encodedUri = encodeURIComponent(uri);
        switch (walletId) {
            case 'safepal':
                return {
                    universal: `https://link.safepal.io/wc?uri=${encodedUri}`,
                    native: `safepalwallet://wc?uri=${encodedUri}`,
                    appScheme: 'safepalwallet://'
                };
            case 'trust':
                return {
                    universal: `https://link.trustwallet.com/wc?uri=${encodedUri}`,
                    native: `trust://wc?uri=${encodedUri}`,
                    appScheme: 'trust://'
                };
            case 'metamask':
                return {
                    universal: `https://metamask.app.link/wc?uri=${encodedUri}`,
                    native: `metamask://wc?uri=${encodedUri}`,
                    appScheme: 'metamask://'
                };
            case 'tokenpocket':
                return {
                    universal: `https://tokenpocket.vip/app?uri=${encodedUri}`,
                    native: `tpoutside://wc?uri=${encodedUri}`,
                    appScheme: 'tpoutside://'
                };
            case 'bitget':
                return {
                    universal: `https://bkapp.vip/wc?uri=${encodedUri}`,
                    native: `bitkeep://wc?uri=${encodedUri}`,
                    appScheme: 'bitkeep://'
                };
            case 'okx':
                return {
                    universal: `https://www.okx.com/download?appendQuery=true&deeplink=${encodeURIComponent('okx://wallet/walletConnect?uri=' + uri)}`,
                    native: `okex://main/wc?uri=${encodedUri}`,
                    appScheme: 'okex://main'
                };
            case 'binance':
                return {
                    universal: `https://app.binance.com/cedefi/wc?uri=${encodedUri}`,
                    native: `bnc://app.binance.com/cedefi/wc?uri=${encodedUri}`,
                    appScheme: 'bnc://app.binance.com/'
                };
            case 'coinbase':
                return {
                    universal: `https://go.cb-w.com/wc?uri=${encodedUri}`,
                    native: `cbwallet://wc?uri=${encodedUri}`,
                    appScheme: 'cbwallet://'
                };
            default:
                return {
                    universal: null,
                    native: `wc:${uri}`,
                    appScheme: 'safepalwallet://'
                };
        }
    }

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

    // 6. Resilient Multi-RPC Fetcher
    async function fetchRpcWithFallback(method, params = []) {
        let lastError = null;
        for (const endpoint of BSC_RPC_ENDPOINTS) {
            try {
                const controller = new AbortController();
                const timeoutId = setTimeout(() => controller.abort(), 6000);

                const response = await fetch(endpoint, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({
                        jsonrpc: '2.0',
                        id: Date.now(),
                        method,
                        params
                    }),
                    signal: controller.signal
                });

                clearTimeout(timeoutId);

                if (!response.ok) continue;

                const data = await response.json();
                if (data && data.result !== undefined) {
                    return data.result;
                }
            } catch (err) {
                lastError = err;
            }
        }
        throw (lastError || new Error('All BSC RPC endpoints unreachable. Please check your internet connection.'));
    }

    // Direct, light-speed USDT Balance check over HTTPS (No eth_blockNumber overhead!)
    async function getUsdtBalance(walletAddress) {
        if (!walletAddress) return 0;
        try {
            // balanceOf(address) selector: 0x70a08231
            const cleanAddr = walletAddress.toLowerCase().replace(/^0x/, '').padStart(64, '0');
            const callData = '0x70a08231' + cleanAddr;

            const hexResult = await fetchRpcWithFallback('eth_call', [
                { to: USDT_CONTRACT_ADDRESS, data: callData },
                'latest'
            ]);

            if (hexResult && hexResult !== '0x') {
                const rawBigInt = BigInt(hexResult);
                // 18 decimals on BEP-20 USDT
                const whole = rawBigInt / 1000000000000000000n;
                const remainder = rawBigInt % 1000000000000000000n;
                const balanceFloat = Number(whole) + Number(remainder) / 1e18;
                return balanceFloat;
            }
            return 0;
        } catch (e) {
            console.warn('Direct USDT balance query note:', e.message);
            return 0;
        }
    }

    // Direct On-Chain Transaction Receipt Poller (Independent of mobile WebSocket)
    async function pollTransactionReceipt(txHash, maxSeconds = 45) {
        const startTime = Date.now();
        while ((Date.now() - startTime) < (maxSeconds * 1000)) {
            try {
                const receipt = await fetchRpcWithFallback('eth_getTransactionReceipt', [txHash]);
                if (receipt && receipt.blockNumber) {
                    if (receipt.status === '0x1') {
                        const latestBlockHex = await fetchRpcWithFallback('eth_blockNumber', []);
                        const receiptBlock = Number.parseInt(receipt.blockNumber, 16);
                        const latestBlock = Number.parseInt(latestBlockHex, 16);
                        const confirmations = latestBlock >= receiptBlock
                            ? latestBlock - receiptBlock + 1
                            : 0;

                        if (confirmations >= 12) {
                            return { success: true, receipt, confirmations };
                        }
                    } else if (receipt.status === '0x0') {
                        return { success: false, reason: 'Transaction failed or was reverted on BSC.' };
                    }
                }
            } catch (pollErr) {
                // Silently wait for next polling round
            }
            await new Promise(r => setTimeout(r, 1500));
        }
        return { success: false, timeout: true };
    }

    // 7. WalletConnect v2 Initialization
    async function getWalletConnectProvider() {
        if (wcProviderPromise) {
            return await wcProviderPromise;
        }

        const ProviderClass = window.WalletConnectEthereumProvider || window.NevoraWCBundle?.EthereumProvider;
        if (!ProviderClass) {
            throw new Error('WalletConnect provider library is not loaded. Please refresh the page.');
        }

        wcProviderPromise = ProviderClass.init({
            projectId: REOWN_PROJECT_ID,
            chains: [BSC_CHAIN_ID],
            optionalChains: [BSC_CHAIN_ID],
            showQrModal: false,
            rpcMap: {
                [BSC_CHAIN_ID]: BSC_RPC_ENDPOINTS[0]
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

    // QR Code Modal for Desktop
    async function showQRCodeModal(uri, walletName = 'Web3 Wallet') {
        let qrDataUrl = '';
        const qrLib = window.NevoraQRCode || window.QRCode;
        if (qrLib && qrLib.toDataURL) {
            try {
                qrDataUrl = await qrLib.toDataURL(uri, { width: 260, margin: 1 });
            } catch (e) {
                console.warn('QR code render notice:', e);
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

    // 8. Connection Handlers
    function normalizeChainId(value) {
        if (typeof value === 'bigint') return Number(value);
        if (typeof value === 'number') return value;
        if (typeof value !== 'string' || value.trim() === '') return NaN;

        const normalized = value.trim().toLowerCase();
        return normalized.startsWith('0x')
            ? Number.parseInt(normalized, 16)
            : Number(normalized);
    }

    async function ensureBscNetwork(provider) {
        if (!provider || typeof provider.request !== 'function') {
            throw new Error('The connected wallet does not expose an EVM provider.');
        }

        const readChainId = async () => normalizeChainId(
            await provider.request({ method: 'eth_chainId' })
        );

        let chainId = await readChainId();
        if (chainId === BSC_CHAIN_ID) return;

        try {
            await provider.request({
                method: 'wallet_switchEthereumChain',
                params: [{ chainId: BSC_CHAIN_ID_HEX }]
            });
        } catch (switchError) {
            if (Number(switchError?.code) !== 4902) {
                throw new Error('Please approve switching your wallet to BNB Smart Chain (Chain ID 56) to continue.');
            }

            await provider.request({
                method: 'wallet_addEthereumChain',
                params: [{
                    chainId: BSC_CHAIN_ID_HEX,
                    chainName: BSC_NAME,
                    nativeCurrency: { name: 'BNB', symbol: 'BNB', decimals: 18 },
                    rpcUrls: [BSC_RPC_ENDPOINTS[0]],
                    blockExplorerUrls: [BLOCK_EXPLORER]
                }]
            });

            // Adding a network does not necessarily switch to it.
            await provider.request({
                method: 'wallet_switchEthereumChain',
                params: [{ chainId: BSC_CHAIN_ID_HEX }]
            });
        }

        // WalletConnect and mobile wallets can update the active chain asynchronously.
        for (let attempt = 0; attempt < 5; attempt++) {
            chainId = await readChainId();
            if (chainId === BSC_CHAIN_ID) return;
            await new Promise(resolve => setTimeout(resolve, 300));
        }

        throw new Error('Your wallet is not connected to BNB Smart Chain (Chain ID 56). No payment was sent.');
    }

    // Connect via direct injected provider (Fastest, 0ms, used for in-app browsers & desktop extensions)
    async function connectViaInjected(wallet) {
        const provider = wallet.getProvider();
        if (!provider) {
            throw new Error(`${wallet.name} extension or in-app provider is not detected.`);
        }

        await ensureBscNetwork(provider);

        const accounts = await provider.request({ method: 'eth_requestAccounts' });
        if (!accounts || accounts.length === 0) {
            throw new Error(`No accounts authorized in ${wallet.name}.`);
        }

        const address = accounts[0].toLowerCase();
        state.connectedAddress = address;
        state.connectedPlatform = wallet.name;
        state.connectedPlatformId = wallet.id;
        state.isWalletConnect = false;
        state.activeWCProvider = null;

        if (window.ethers) {
            // Static Network locks chain 56 and suppresses internal eth_blockNumber queries!
            state.provider = new ethers.BrowserProvider(provider, BSC_STATIC_NETWORK, { staticNetwork: true });
            state.signer = await state.provider.getSigner();
        }

        localStorage.setItem('nevora_connected_platform', wallet.name);
        localStorage.setItem('nevora_connected_platform_id', wallet.id);
        localStorage.setItem('nevora_connected_address', address);

        updateRegistrationTopBar(address, wallet.name, wallet.iconSvg);

        return { address, wallet, provider, isWalletConnect: false };
    }

    // Connect via WalletConnect v2
    async function connectViaWalletConnect(walletId = 'safepal', forceFresh = false) {
        const wallet = SUPPORTED_WALLETS.find(w => w.id === walletId) || SUPPORTED_WALLETS[0];
        const wc = await getWalletConnectProvider();

        // Disconnect only if platform switched or explicitly forced
        if (wc.session && (forceFresh || (state.connectedPlatformId && state.connectedPlatformId !== wallet.id))) {
            try {
                await wc.disconnect();
            } catch (e) {}
        }

        // Reuse active session only after confirming its active chain.
        if (wc.session && wc.accounts && wc.accounts.length > 0 && !forceFresh) {
            await ensureBscNetwork(wc);
            const address = wc.accounts[0].toLowerCase();
            state.connectedAddress = address;
            state.connectedPlatform = wallet.name;
            state.connectedPlatformId = wallet.id;
            state.activeWCProvider = wc;
            state.isWalletConnect = true;

            if (window.ethers) {
                state.provider = new ethers.BrowserProvider(wc, BSC_STATIC_NETWORK, { staticNetwork: true });
                state.signer = await state.provider.getSigner();
            }

            localStorage.setItem('nevora_connected_platform', wallet.name);
            localStorage.setItem('nevora_connected_platform_id', wallet.id);
            localStorage.setItem('nevora_connected_address', address);

            updateRegistrationTopBar(address, wallet.name, wallet.iconSvg);
            return { address, wallet, provider: wc, isWalletConnect: true };
        }

        const uriHandler = (uri) => {
            if (isMobileDevice()) {
                const links = getWalletDeepLinks(wallet.id, uri);
                const primaryUrl = links.native || links.universal;

                launchNativeScheme(primaryUrl);

                Swal.fire({
                    title: `Connecting to ${wallet.name}...`,
                    html: `
                        <div class="my-3 text-center">
                            <div class="spinner-border text-primary" role="status" style="width: 2.8rem; height: 2.8rem;"></div>
                        </div>
                        <p class="font-13 text-muted mb-2">We opened <strong>${wallet.name}</strong> on your phone.</p>
                        <p class="font-12 text-muted mb-3">Please unlock your app and tap <strong>Connect</strong>.</p>
                        <div class="d-flex flex-column gap-2">
                            <a href="${primaryUrl}" class="btn btn-primary btn-sm rounded-pill py-2 fw-semibold shadow-sm d-flex align-items-center justify-content-center gap-1.5" id="btn-open-wallet-primary">
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
                    customClass: { popup: 'rounded-4 p-3 shadow-lg' },
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
                showQRCodeModal(uri, wallet.name);
            }
        };

        wc.on('display_uri', uriHandler);

        try {
            await wc.connect();

            if (!wc.accounts || wc.accounts.length === 0) {
                throw new Error('No accounts authorized in your wallet.');
            }

            await ensureBscNetwork(wc);
            const address = wc.accounts[0].toLowerCase();
            state.connectedAddress = address;
            state.connectedPlatform = wallet.name;
            state.connectedPlatformId = wallet.id;
            state.activeWCProvider = wc;
            state.isWalletConnect = true;

            if (window.ethers) {
                state.provider = new ethers.BrowserProvider(wc, BSC_STATIC_NETWORK, { staticNetwork: true });
                state.signer = await state.provider.getSigner();
            }

            localStorage.setItem('nevora_connected_platform', wallet.name);
            localStorage.setItem('nevora_connected_platform_id', wallet.id);
            localStorage.setItem('nevora_connected_address', address);

            updateRegistrationTopBar(address, wallet.name, wallet.iconSvg);

            return { address, wallet, provider: wc, isWalletConnect: true };
        } finally {
            wc.removeListener('display_uri', uriHandler);
        }
    }

    // Unified connect entry point
    async function connectByPlatform(walletId = 'safepal', forceFresh = false) {
        const wallet = SUPPORTED_WALLETS.find(w => w.id === walletId) || SUPPORTED_WALLETS[0];

        // 1. If in-app wallet browser or injected provider exists, use direct injected connection
        const injected = wallet.getProvider();
        if (injected) {
            return await connectViaInjected(wallet);
        }

        // 2. If inside mobile DApp browser of another wallet, allow fallback to window.ethereum
        if (isInAppWalletBrowser() && window.ethereum) {
            return await connectViaInjected({
                ...wallet,
                getProvider: () => window.ethereum
            });
        }

        // 3. Otherwise, use WalletConnect v2
        return await connectViaWalletConnect(wallet.id, forceFresh);
    }

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
        } catch (e) {}

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

    // 9. UI Sync & Modal Display
    function updateRegistrationTopBar(address, platformName = 'SafePal', iconSvg = null) {
        const idleBox = document.getElementById('wallet-connect-idle');
        const activeBox = document.getElementById('wallet-connect-active');
        const addressDisplay = document.getElementById('top-wallet-address-display');
        const platformBadge = document.getElementById('top-wallet-platform-badge');
        const walletHiddenInput = document.getElementById('wallet_address');
        const iconContainer = document.getElementById('top-wallet-icon-box');

        if (!address) {
            if (idleBox) idleBox.classList.remove('d-none');
            if (activeBox) activeBox.classList.add('d-none');
            if (walletHiddenInput) walletHiddenInput.value = '';
            return;
        }

        const masked = address.slice(0, 6) + '...' + address.slice(-4);
        if (addressDisplay) addressDisplay.textContent = masked;
        if (platformBadge) platformBadge.textContent = `${platformName} Linked`;
        if (walletHiddenInput) walletHiddenInput.value = address.toLowerCase();

        if (iconContainer && iconSvg) {
            iconContainer.innerHTML = iconSvg;
        }

        if (idleBox) idleBox.classList.add('d-none');
        if (activeBox) activeBox.classList.remove('d-none');
    }

    function showWalletModal(options = {}) {
        const title = options.title || 'Connect Web3 Wallet';
        const subtitle = options.subtitle || 'Select your wallet app to link with NEVORA';

        const isMobile = isMobileDevice();
        const popupClass = isMobile ? 'nevora-wallet-drawer-popup' : 'nevora-wallet-modal-popup';
        const position = isMobile ? 'bottom' : 'center';

        const cardsHtml = SUPPORTED_WALLETS.map(w => {
            const hasInjected = !!w.getProvider();
            return `
                <div class="col-6 col-sm-4">
                    <div class="wallet-option-card rounded-4 p-2.5 text-center h-100 d-flex flex-column align-items-center justify-content-between position-relative" data-wallet-id="${w.id}">
                        ${hasInjected ? `
                            <span class="position-absolute top-0 end-0 translate-middle-y me-2 badge bg-success text-white font-9 px-1.5 py-0.5 rounded-pill shadow-xs">
                                Ready
                            </span>
                        ` : ''}
                        <div class="wallet-icon-box rounded-circle shadow-xs d-flex align-items-center justify-content-center mb-1.5" style="width: 44px; height: 44px; background: rgba(0,0,0,0.03);">
                            ${w.iconSvg}
                        </div>
                        <div class="w-100">
                            <span class="font-12 fw-bold text-dark d-block text-truncate mb-0.5">${w.name}</span>
                            <span class="badge ${w.badgeClass || 'bg-light text-muted'} font-9 px-1.5 py-0.5 rounded-pill mb-1 d-inline-block">
                                ${w.badge || 'BEP-20'}
                            </span>
                        </div>
                    </div>
                </div>
            `;
        }).join('');

        Swal.fire({
            title: `<span class="fw-bold font-17">${title}</span>`,
            html: `
                <p class="text-muted font-12 mb-3">${subtitle}</p>
                <div class="row g-2 text-start" id="wallet-modal-grid">
                    ${cardsHtml}
                </div>
                <div class="mt-3 pt-2 border-top text-center">
                    <small class="text-muted font-11">
                        <i class="fa-solid fa-shield-halved text-success me-1"></i> Secured by BNB Smart Chain (BEP-20)
                    </small>
                </div>
            `,
            showConfirmButton: false,
            showCloseButton: true,
            position: position,
            customClass: {
                popup: `rounded-4 p-3 shadow-lg ${popupClass}`
            },
            didOpen: () => {
                const grid = document.getElementById('wallet-modal-grid');
                if (grid) {
                    grid.querySelectorAll('.wallet-option-card').forEach(card => {
                        card.addEventListener('click', async () => {
                            const walletId = card.getAttribute('data-wallet-id');
                            const wallet = SUPPORTED_WALLETS.find(w => w.id === walletId);
                            Swal.close();
                            if (typeof options.onSelect === 'function') {
                                options.onSelect(walletId, wallet);
                            } else {
                                await connectByPlatform(walletId);
                            }
                        });
                    });
                }
            }
        });
    }

    // 10. Atomic Pay-to-Register Flow
    async function executePayToRegister(formInputs) {
        if (isRegistrationActive) return;
        isRegistrationActive = true;

        try {
            let connectedAddress = state.connectedAddress;
            let wallet = SUPPORTED_WALLETS.find(w => w.id === state.connectedPlatformId) || SUPPORTED_WALLETS[0];

            if (!connectedAddress) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Wallet Not Connected',
                    text: 'Please connect your Web3 wallet first before proceeding with payment.',
                    confirmButtonText: 'Connect Now'
                }).then(() => {
                    const btn = document.getElementById('btn-top-connect-wallet');
                    if (btn) btn.click();
                });
                return;
            }

            // Sync hidden form input
            const walletHidden = document.getElementById('wallet_address');
            if (walletHidden) walletHidden.value = connectedAddress;

            // 1. Verify USDT Balance on BSC (No eth_blockNumber calls!)
            Swal.fire({
                title: 'Checking USDT Balance...',
                html: '<div class="spinner-border text-primary my-3" role="status"></div><p class="font-12 text-muted">Querying BNB Smart Chain...</p>',
                allowOutsideClick: false,
                showConfirmButton: false,
            });

            const usdtBalance = await getUsdtBalance(connectedAddress);
            const requiredFee = typeof window.REGISTRATION_FEE !== 'undefined' ? parseFloat(window.REGISTRATION_FEE) : 0.10;

            if (usdtBalance < requiredFee) {
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
                                <span class="fw-bold text-primary font-monospace">$${requiredFee.toFixed(2)} USDT</span>
                            </div>
                        </div>
                        <p class="font-12 text-muted mb-1">Your balance is not sufficient to complete registration.</p>
                        <p class="font-11 text-muted mb-0">Please deposit at least $${requiredFee.toFixed(2)} USDT (BEP-20) and a little BNB for gas into your wallet, then try again.</p>
                    `,
                    confirmButtonText: 'OK',
                    confirmButtonColor: '#2563eb'
                });
                return;
            }

            // 2. Fetch Payee Route Preview
            Swal.fire({
                title: 'Resolving Payee Route...',
                html: '<div class="spinner-border text-primary my-3" role="status"></div><p class="font-12 text-muted">Calculating 3-matrix eligible beneficiary...</p>',
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
                body: JSON.stringify({ sponsor_id: formInputs.sponsor_id })
            });

            const payeeData = await payeeRes.json();
            if (!payeeData || !payeeData.status) {
                throw new Error(payeeData?.message || 'Could not resolve payment payee route.');
            }

            const payeeAddress = payeeData.payee_address;
            const payeeMemberId = payeeData.payee_member_id;
            const payeeMasked = payeeData.payee_masked_address || (payeeAddress.slice(0, 6) + '...' + payeeAddress.slice(-4));
            const amountUsdt = payeeData.amount || requiredFee;
            const amountWei = BigInt(payeeData.amount_wei || `${Math.round(amountUsdt * 1e18)}`);
            const appScheme = getWalletAppScheme(wallet.id);

            // Stash pending registration details in localStorage
            // Never persist passwords or other credentials in browser storage.
            // Recovery asks the user to re-enter the password after returning from the wallet.
            const pendingReg = {
                sponsor_id: formInputs.sponsor_id,
                name: formInputs.name,
                email: formInputs.email,
                wallet_address: connectedAddress,
                amount: amountUsdt,
                payee_address: payeeAddress,
                timestamp: Date.now()
            };
            localStorage.setItem('nevora_pending_registration', JSON.stringify(pendingReg));

            // 3. Prompt user for signature in wallet
            Swal.fire({
                title: `Authorize $${parseFloat(amountUsdt).toFixed(2)} USDT in Wallet`,
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
                cancelButtonText: 'Cancel Registration'
            });

            // 4. Construct Provider with Static Network (Disables internal eth_blockNumber calls!)
            const activeRawProvider = state.activeWCProvider || (wallet.getProvider() || window.ethereum);
            if (!activeRawProvider) {
                throw new Error('Active Web3 wallet provider disconnected. Please reconnect your wallet.');
            }

            await ensureBscNetwork(activeRawProvider);
            const browserProvider = new ethers.BrowserProvider(activeRawProvider, BSC_STATIC_NETWORK, { staticNetwork: true });
            const signer = await browserProvider.getSigner();
            const signerAddress = (await signer.getAddress()).toLowerCase();
            if (signerAddress !== connectedAddress.toLowerCase()) {
                throw new Error('The active wallet account changed. Reconnect the intended wallet before paying.');
            }
            const usdtContract = new ethers.Contract(USDT_CONTRACT_ADDRESS, BEP20_ABI, signer);

            // Fast gas overrides avoid slow estimateGas round-trips over mobile connection
            const txOverrides = {
                gasLimit: 100000n
            };

            if (isMobileDevice() && state.isWalletConnect && appScheme) {
                setTimeout(() => { launchNativeScheme(appScheme); }, 200);
            }

            // 5. Broadcast Transaction
            const tx = await usdtContract.transfer(payeeAddress, amountWei, txOverrides);

            // Immediately save tx_hash to safety net
            pendingReg.tx_hash = tx.hash;
            localStorage.setItem('nevora_pending_registration', JSON.stringify(pendingReg));

            // 6. Direct HTTPS Receipt Polling (Does not hang on mobile background sleep!)
            Swal.fire({
                title: 'Validating on BSC...',
                html: `
                    <div class="my-3 text-center">
                        <div class="spinner-border text-success" role="status" style="width: 3rem; height: 3rem;"></div>
                    </div>
                    <p class="font-13 text-muted mb-2">Payment broadcasted successfully!</p>
                    <p class="font-monospace font-11 text-muted text-break p-2 bg-light rounded border">${tx.hash}</p>
                    <small class="text-muted font-11"><i class="fa-solid fa-clock me-1"></i> Waiting for 12 BNB Smart Chain confirmations before account activation...</small>
                `,
                allowOutsideClick: false,
                showConfirmButton: false,
            });

            const pollResult = await pollTransactionReceipt(tx.hash, 45);

            if (!pollResult.success) {
                throw new Error(pollResult.reason || 'The payment is still awaiting 12 confirmations. Your transaction is saved; return to this page and complete registration once confirmations are reached.');
            }

            // 7. Atomic User Registration Submission
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

                const memberName = regData.name || formInputs?.name || '';
                const sponsorMemberId = regData.sponsor_id || formInputs?.sponsor_id || '';

                Swal.fire({
                    icon: 'success',
                    title: 'Registration & Activation Successful! 🎉',
                    html: `
                        <p class="text-muted font-13 mb-3">${regData.message || 'Your account is active on Slot 1.'}</p>
                        <div class="p-3 bg-light rounded-4 border text-start mb-3 font-12">
                            <div class="d-flex justify-content-between mb-1.5 pb-1 border-bottom">
                                <span class="text-muted">New Member ID:</span>
                                <span class="fw-bold font-monospace text-primary font-14">${regData.member_id}</span>
                            </div>
                            <div class="d-flex justify-content-between mb-1.5 pb-1 border-bottom">
                                <span class="text-muted">Full Name:</span>
                                <span class="fw-bold text-dark">${memberName}</span>
                            </div>
                            <div class="d-flex justify-content-between mb-1.5 pb-1 border-bottom">
                                <span class="text-muted">Sponsor ID:</span>
                                <span class="fw-bold font-monospace text-primary">${sponsorMemberId}</span>
                            </div>
                            <div class="d-flex justify-content-between">
                                <span class="text-muted">Status:</span>
                                <span class="badge bg-success text-white px-2 py-0.5 rounded-pill"><i class="fa-solid fa-circle-check me-1"></i>Slot 1 Paid & Active</span>
                            </div>
                        </div>
                    `,
                    confirmButtonText: 'Go to Dashboard',
                    confirmButtonColor: '#198754',
                    allowOutsideClick: false
                }).then(() => {
                    window.location.href = '/member/dashboard';
                });
            } else {
                throw new Error(regData?.message || 'Backend registration failed. Please refresh and click Complete Registration.');
            }

        } catch (err) {
            console.error('Registration payment execution notice:', err);

            if (err.code === 4001 || err.code === 'ACTION_REJECTED' || err.message?.includes('rejected') || err.message?.includes('denied')) {
                Swal.fire({
                    icon: 'info',
                    title: 'Payment Cancelled',
                    text: `You declined the $${parseFloat(amountUsdt || requiredFee).toFixed(2)} USDT transfer in your wallet app.`,
                    confirmButtonText: 'OK'
                });
            } else {
                // Friendly error translation (Never dump raw eth_blockNumber payloads!)
                let friendlyMsg = err.reason || err.message || 'Could not complete registration payment.';
                if (friendlyMsg.includes('Failed to fetch') || friendlyMsg.includes('coalesce')) {
                    friendlyMsg = 'Network connection interrupted. Please ensure your wallet is connected to BNB Smart Chain and try again.';
                }

                Swal.fire({
                    icon: 'error',
                    title: 'Payment Could Not Complete',
                    text: friendlyMsg,
                    confirmButtonText: 'Try Again'
                });
            }
        } finally {
            isRegistrationActive = false;
        }
    }

    // 11. Member Dashboard Payment Modal (Slot 2-12 Upgrade)
    async function openPaymentModal(slotNumber = 1) {
        try {
            let connectedAddress = state.connectedAddress;
            let wallet = SUPPORTED_WALLETS.find(w => w.id === state.connectedPlatformId) || SUPPORTED_WALLETS[0];

            if (!connectedAddress) {
                showWalletModal({
                    title: 'Connect Wallet to Pay',
                    subtitle: 'Choose your wallet to activate this matrix slot',
                    onSelect: async (wId) => {
                        await connectByPlatform(wId);
                        openPaymentModal(slotNumber);
                    }
                });
                return;
            }

            // Fetch payment route info for this slot
            Swal.fire({
                title: 'Loading Slot Details...',
                html: '<div class="spinner-border text-primary my-3" role="status"></div>',
                allowOutsideClick: false,
                showConfirmButton: false,
            });

            const routeRes = await fetch(`/member/payment-route-info?slot_number=${slotNumber}`);
            const routeData = await routeRes.json();

            if (!routeData || !routeData.status) {
                throw new Error(routeData?.message || 'Could not resolve slot payment route.');
            }

            const payeeAddress = routeData.payee_address;
            const payeeMemberId = routeData.payee_member_id;
            const payeeMasked = routeData.payee_masked_address || (payeeAddress.slice(0, 6) + '...' + payeeAddress.slice(-4));
            const amountUsdt = routeData.amount;
            const amountWei = BigInt(routeData.amount_wei || `${amountUsdt * 1e18}`);
            const slotName = routeData.slot_name || `Slot ${slotNumber}`;
            const appScheme = getWalletAppScheme(wallet.id);

            // Check USDT Balance
            const balance = await getUsdtBalance(connectedAddress);
            if (balance < parseFloat(amountUsdt)) {
                Swal.fire({
                    icon: 'error',
                    title: 'Insufficient Balance',
                    html: `
                        <p class="font-13 text-muted">You have <strong class="text-danger font-monospace">$${balance.toFixed(2)} USDT</strong> in your wallet, but <strong>${slotName}</strong> requires <strong class="text-primary font-monospace">$${parseFloat(amountUsdt).toFixed(2)} USDT</strong>.</p>
                        <p class="font-11 text-muted mb-0">Please deposit USDT on BNB Smart Chain and try again.</p>
                    `,
                    confirmButtonText: 'OK'
                });
                return;
            }

            // Confirm Payment
            Swal.fire({
                title: `Activate ${slotName}`,
                html: `
                    <div class="p-3 bg-light rounded-4 text-start mb-3 border">
                        <div class="d-flex justify-content-between font-12 mb-1.5 pb-1 border-bottom">
                            <span class="text-muted">Slot Fee:</span>
                            <span class="fw-bold text-success font-14">$${parseFloat(amountUsdt).toFixed(2)} USDT</span>
                        </div>
                        <div class="d-flex justify-content-between font-12 mb-1.5 pb-1 border-bottom">
                            <span class="text-muted">Beneficiary Member ID:</span>
                            <span class="fw-bold font-monospace text-primary">${payeeMemberId}</span>
                        </div>
                        <div class="d-flex justify-content-between font-12 mb-1.5 pb-1 border-bottom">
                            <span class="text-muted">Beneficiary Wallet:</span>
                            <span class="fw-bold font-monospace text-dark">${payeeMasked}</span>
                        </div>
                        <div class="d-flex justify-content-between font-12">
                            <span class="text-muted">Network:</span>
                            <span class="fw-bold text-dark">BNB Smart Chain (BEP-20)</span>
                        </div>
                    </div>
                    <div class="d-flex align-items-center justify-content-center gap-2 text-primary font-13 fw-semibold mb-3">
                        <i class="fa-solid fa-fingerprint fa-bounce fa-lg"></i>
                        <span>Please authorize transfer in ${wallet.name}...</span>
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
                cancelButtonText: 'Cancel'
            });

            const activeRawProvider = state.activeWCProvider || (wallet.getProvider() || window.ethereum);
            const browserProvider = new ethers.BrowserProvider(activeRawProvider, BSC_STATIC_NETWORK, { staticNetwork: true });
            const signer = await browserProvider.getSigner();
            const usdtContract = new ethers.Contract(USDT_CONTRACT_ADDRESS, BEP20_ABI, signer);

            if (isMobileDevice() && state.isWalletConnect && appScheme) {
                setTimeout(() => { launchNativeScheme(appScheme); }, 200);
            }

            const tx = await usdtContract.transfer(payeeAddress, amountWei, { gasLimit: 100000n });

            Swal.fire({
                title: 'Validating on BSC...',
                html: `
                    <div class="my-3 text-center">
                        <div class="spinner-border text-success" role="status" style="width: 3rem; height: 3rem;"></div>
                    </div>
                    <p class="font-13 text-muted mb-2">Payment broadcasted successfully!</p>
                    <p class="font-monospace font-11 text-muted text-break p-2 bg-light rounded border">${tx.hash}</p>
                    <small class="text-muted font-11"><i class="fa-solid fa-clock me-1"></i> Waiting for 12 BNB Smart Chain confirmations...</small>
                `,
                allowOutsideClick: false,
                showConfirmButton: false,
            });

            await pollTransactionReceipt(tx.hash, 45);

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

            if (trackData && trackData.status) {
                Swal.fire({
                    icon: 'success',
                    title: `${slotName} Activated! 🎉`,
                    html: `
                        <p class="text-muted font-13">${trackData.message || 'Payment confirmed on BSC.'}</p>
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
                    text: trackData?.message || 'Payment broadcasted on BSC. Your slot will be updated in a moment.',
                    confirmButtonText: 'Refresh Page'
                }).then(() => {
                    window.location.reload();
                });
            }

        } catch (err) {
            console.error('Slot upgrade execution notice:', err);
            if (err.code === 4001 || err.code === 'ACTION_REJECTED' || err.message?.includes('rejected')) {
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

    // 12. Safety Net: Resume Interrupted Registration
    async function resumePendingRegistration() {
        if (typeof window === 'undefined') return false;
        const pendingRaw = localStorage.getItem('nevora_pending_registration');
        if (!pendingRaw) return false;

        try {
            const pending = JSON.parse(pendingRaw);
            if (!pending || !pending.tx_hash) return false;

            // Clean credentials written by older versions of the registration flow.
            delete pending.password;
            delete pending.password_confirmation;
            localStorage.setItem('nevora_pending_registration', JSON.stringify(pending));

            // Expire after 2 hours
            if (Date.now() - pending.timestamp > 2 * 3600 * 1000) {
                localStorage.removeItem('nevora_pending_registration');
                return false;
            }

            Swal.fire({
                title: 'Pending Payment Detected',
                html: `
                    <p class="font-13 text-muted mb-2">We detected a broadcasted registration payment from your wallet!</p>
                    <p class="font-monospace font-11 text-break p-2 bg-light rounded border text-muted">${pending.tx_hash}</p>
                    <p class="font-12 text-muted mb-0">Would you like to finalize your account registration now?</p>
                `,
                showCancelButton: true,
                confirmButtonText: 'Complete Registration',
                cancelButtonText: 'Dismiss',
                confirmButtonColor: '#198754',
                allowOutsideClick: false
            }).then(async (result) => {
                if (result.isConfirmed) {
                    const passwordResult = await Swal.fire({
                        title: 'Confirm Your Registration Password',
                        input: 'password',
                        inputLabel: 'Re-enter the password you chose during registration',
                        inputPlaceholder: 'Registration password',
                        inputAttributes: { autocomplete: 'new-password', autocapitalize: 'off' },
                        showCancelButton: true,
                        confirmButtonText: 'Continue',
                        inputValidator: (value) => !value ? 'Please enter your registration password.' : undefined
                    });

                    if (!passwordResult.isConfirmed) return;

                    const confirmationResult = await Swal.fire({
                        title: 'Confirm Password',
                        input: 'password',
                        inputLabel: 'Enter the same password again',
                        inputPlaceholder: 'Confirm registration password',
                        inputAttributes: { autocomplete: 'new-password', autocapitalize: 'off' },
                        showCancelButton: true,
                        confirmButtonText: 'Finalize Registration',
                        inputValidator: (value) => {
                            if (!value) return 'Please confirm your password.';
                            if (value !== passwordResult.value) return 'The passwords do not match.';
                            return undefined;
                        }
                    });

                    if (!confirmationResult.isConfirmed) return;

                    Swal.fire({
                        title: 'Finalizing Account...',
                        html: '<div class="spinner-border text-success my-3" role="status"></div>',
                        allowOutsideClick: false,
                        showConfirmButton: false,
                    });

                    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
                    const recoveryPayload = {
                        ...pending,
                        password: passwordResult.value,
                        password_confirmation: confirmationResult.value
                    };
                    const res = await fetch('/register', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': csrfToken
                        },
                        body: JSON.stringify(recoveryPayload)
                    });

                    const data = await res.json();
                    if (data && data.status) {
                        localStorage.removeItem('nevora_pending_registration');
                        Swal.fire({
                            icon: 'success',
                            title: 'Registration Complete! 🎉',
                            text: data.message,
                            confirmButtonText: 'Go to Dashboard'
                        }).then(() => {
                            window.location.href = '/member/dashboard';
                        });
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Registration Could Not Complete',
                            text: data?.message || 'Could not verify transaction on BSC yet. Please wait a moment and refresh.'
                        });
                    }
                }
            });

            return true;
        } catch (e) {
            return false;
        }
    }

    // 13. Auto Session Restore on Page Load
    async function initAutoSession() {
        try {
            const savedAddress = localStorage.getItem('nevora_connected_address');
            const savedPlatform = localStorage.getItem('nevora_connected_platform');
            const savedPlatformId = localStorage.getItem('nevora_connected_platform_id');

            // If inside in-app wallet browser, detect active account
            if (isInAppWalletBrowser() && window.ethereum) {
                const accounts = await window.ethereum.request({ method: 'eth_accounts' }).catch(() => []);
                if (accounts && accounts.length > 0) {
                    const addr = accounts[0].toLowerCase();
                    const detectedWallet = SUPPORTED_WALLETS.find(w => w.id === savedPlatformId) || SUPPORTED_WALLETS[0];
                    state.connectedAddress = addr;
                    state.connectedPlatform = detectedWallet.name;
                    state.connectedPlatformId = detectedWallet.id;
                    state.isWalletConnect = false;

                    if (window.ethers) {
                        state.provider = new ethers.BrowserProvider(window.ethereum, BSC_STATIC_NETWORK, { staticNetwork: true });
                        state.signer = await state.provider.getSigner().catch(() => null);
                    }

                    updateRegistrationTopBar(addr, detectedWallet.name, detectedWallet.iconSvg);
                    return;
                }
            }

            // Restore from saved storage
            if (savedAddress && savedPlatformId) {
                const walletObj = SUPPORTED_WALLETS.find(w => w.id === savedPlatformId);
                const injected = walletObj?.getProvider();

                if (injected) {
                    const accounts = await injected.request({ method: 'eth_accounts' }).catch(() => []);
                    if (accounts && accounts.length > 0) {
                        state.connectedAddress = accounts[0].toLowerCase();
                        state.connectedPlatform = walletObj.name;
                        state.connectedPlatformId = walletObj.id;
                        state.isWalletConnect = false;

                        if (window.ethers) {
                            state.provider = new ethers.BrowserProvider(injected, BSC_STATIC_NETWORK, { staticNetwork: true });
                            state.signer = await state.provider.getSigner().catch(() => null);
                        }

                        updateRegistrationTopBar(state.connectedAddress, walletObj.name, walletObj.iconSvg);
                        return;
                    }
                }

                // Restore WalletConnect session if valid
                const wc = await getWalletConnectProvider().catch(() => null);
                if (wc && wc.session && wc.accounts && wc.accounts.length > 0) {
                    state.connectedAddress = wc.accounts[0].toLowerCase();
                    state.connectedPlatform = savedPlatform || 'WalletConnect';
                    state.connectedPlatformId = savedPlatformId;
                    state.activeWCProvider = wc;
                    state.isWalletConnect = true;

                    if (window.ethers) {
                        state.provider = new ethers.BrowserProvider(wc, BSC_STATIC_NETWORK, { staticNetwork: true });
                        state.signer = await state.provider.getSigner().catch(() => null);
                    }

                    updateRegistrationTopBar(state.connectedAddress, state.connectedPlatform, walletObj?.iconSvg);
                }
            }
        } catch (e) {
            console.debug('Auto session initialization notice:', e.message);
        }
    }

    // App-Switch Recovery: check pending registration when user refocuses tab
    if (typeof window !== 'undefined') {
        const checkFocus = () => {
            const raw = localStorage.getItem('nevora_pending_registration');
            if (raw) {
                resumePendingRegistration();
            }
        };
        window.addEventListener('focus', checkFocus);
        document.addEventListener('visibilitychange', () => {
            if (document.visibilityState === 'visible') {
                checkFocus();
            }
        });

        if (document.readyState === 'loading') {
            window.addEventListener('DOMContentLoaded', initAutoSession);
        } else {
            initAutoSession();
        }

        setTimeout(initAutoSession, 300);
    }

    // 14. Public API Namespace Export
    return {
        SUPPORTED_WALLETS,
        getState: () => ({ ...state }),
        showWalletModal,
        connectByPlatform,
        connectViaInjected,
        connectViaWalletConnect,
        connectWallet: async (autoBind = true) => {
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
        },
        disconnectRegistrationWallet: () => disconnectWallet(true),
        disconnectWallet,
        updateRegistrationTopBar,
        getUsdtBalance,
        executePayToRegister,
        openPaymentModal,
        resumePendingRegistration,
        launchNativeScheme,
        getWalletAppScheme,
        getWalletDeepLinks,
        fetchRpcWithFallback
    };
})();

// Global window registration
if (typeof window !== 'undefined') {
    window.NevoraWeb3 = NevoraWeb3;
}
