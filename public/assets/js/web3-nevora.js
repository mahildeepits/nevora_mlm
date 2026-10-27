/**
 * NEVORA Web3 & SafePal Automated Integration Engine
 * Supports: SafePal (DApp Browser, Extension & Deep-Link), TrustWallet, MetaMask, TokenPocket
 */

const NevoraWeb3 = (function () {
    // Config state
    let state = {
        chainId: 56, // BSC Mainnet
        chainIdHex: '0x38',
        chainName: 'BNB Smart Chain',
        rpcUrl: 'https://bsc-dataseed.binance.org',
        blockExplorer: 'https://bscscan.com',
        usdtContractAddress: '0x55d398326f99059fF775485246999027B3197955',
        reownProjectId: '392a8f68970ccad5608b6d43cc04cc8c',
        connectedAddress: null,
        provider: null,
        signer: null,
    };

    // Minimal ERC-20 / BEP-20 ABI for Transfer and Balance
    const BEP20_ABI = [
        "function name() view returns (string)",
        "function symbol() view returns (string)",
        "function decimals() view returns (uint8)",
        "function balanceOf(address owner) view returns (uint256)",
        "function transfer(address to, uint256 amount) returns (bool)",
        "event Transfer(address indexed from, address indexed to, uint256 value)"
    ];

    /**
     * Get injected Ethereum provider (SafePal, MetaMask, Trust, etc.)
     */
    function getInjectedProvider() {
        if (window.safepalProvider) {
            return window.safepalProvider;
        }
        if (window.ethereum) {
            // Handle multiple injected providers (e.g. SafePal + MetaMask)
            if (window.ethereum.providers?.length) {
                const sfp = window.ethereum.providers.find(p => p.isSafePal);
                if (sfp) return sfp;
                return window.ethereum.providers[0];
            }
            return window.ethereum;
        }
        return null;
    }

    /**
     * Check if device is mobile
     */
    function isMobileDevice() {
        return /Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent);
    }

    /**
     * Connect Wallet
     */
    async function connectWallet(autoBind = true) {
        const injected = getInjectedProvider();

        if (!injected) {
            if (isMobileDevice()) {
                // Open directly inside SafePal DApp browser
                const currentUrl = encodeURIComponent(window.location.href);
                const sfpLink = `https://link.safepal.io/dapp?url=${currentUrl}`;
                
                Swal.fire({
                    title: 'Open in SafePal App',
                    html: `
                        <p class="text-muted font-13 mb-3">To connect and automate payments, please open this platform inside your <b>SafePal Mobile App</b>.</p>
                        <a href="${sfpLink}" class="btn btn-primary rounded-pill px-4 py-2 fw-bold w-100 mb-2">
                            <i class="fa-solid fa-arrow-up-right-from-square me-1"></i> Open SafePal App
                        </a>
                    `,
                    showConfirmButton: false,
                    showCloseButton: true,
                });
                return null;
            } else {
                Swal.fire({
                    icon: 'warning',
                    title: 'SafePal Wallet Not Found',
                    html: `
                        <p class="text-muted font-13 mb-3">No Web3 wallet extension detected in your browser.</p>
                        <a href="https://www.safepal.com/download" target="_blank" class="btn btn-primary rounded-pill px-4 py-2 fw-bold mb-2">
                            Install SafePal Extension
                        </a>
                    `,
                    confirmButtonText: 'Got It'
                });
                return null;
            }
        }

        try {
            // Request accounts
            const accounts = await injected.request({ method: 'eth_requestAccounts' });
            if (!accounts || accounts.length === 0) {
                throw new Error('No accounts selected');
            }

            const address = accounts[0].toLowerCase();
            state.connectedAddress = address;

            // Ensure connected to BSC
            await checkAndSwitchNetwork(injected);

            // Initialize Ethers provider
            if (window.ethers) {
                state.provider = new ethers.BrowserProvider(injected);
                state.signer = await state.provider.getSigner();
            }

            // Auto-bind to backend if authenticated
            if (autoBind) {
                await bindWalletToBackend(address);
            }

            // Update UI elements
            updateUIWithAddress(address);

            return address;
        } catch (err) {
            console.error('Wallet connection error:', err);
            if (err.code !== 4001) { // 4001 = user rejected request
                toastr.error(err.message || 'Failed to connect wallet');
            }
            return null;
        }
    }

    /**
     * Check and switch network to BNB Smart Chain
     */
    async function checkAndSwitchNetwork(injected) {
        try {
            const currentChainId = await injected.request({ method: 'eth_chainId' });
            if (parseInt(currentChainId, 16) !== state.chainId) {
                try {
                    await injected.request({
                        method: 'wallet_switchEthereumChain',
                        params: [{ chainId: state.chainIdHex }]
                    });
                } catch (switchError) {
                    if (switchError.code === 4902) {
                        await injected.request({
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
     * Bind connected wallet address to user account in Laravel backend
     */
    async function bindWalletToBackend(address) {
        try {
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
            const res = await fetch('/member/profile/bind-web3-wallet', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrfToken
                },
                body: JSON.stringify({ wallet_address: address })
            });

            const data = await res.json();
            if (data.status) {
                toastr.success(data.message || 'SafePal wallet linked successfully!');
                const walletDisplay = document.getElementById('connected-wallet-pill');
                if (walletDisplay) {
                    walletDisplay.classList.remove('d-none');
                    const textSpan = walletDisplay.querySelector('.wallet-text');
                    if (textSpan) textSpan.innerText = data.masked_address || address;
                }
            }
        } catch (e) {
            console.error('Failed to auto-bind wallet:', e);
        }
    }

    /**
     * Update UI elements after connection
     */
    function updateUIWithAddress(address) {
        const masked = address.slice(0, 6) + '...' + address.slice(-4);
        
        // Update any bind inputs
        document.querySelectorAll('.web3-wallet-input').forEach(el => el.value = address);
        
        // Update buttons with status
        document.querySelectorAll('.btn-connect-safepal').forEach(btn => {
            btn.innerHTML = `<i class="fa-solid fa-circle-check text-success me-1"></i> Connected: ${masked}`;
            btn.classList.remove('btn-primary', 'btn-warning');
            btn.classList.add('btn-outline-success');
        });
    }

    /**
     * 1-Click Automated Payment Flow (Matches B-TITAN Experience)
     * @param {number} slotNumber 
     */
    async function paySlotWithSafePal(slotNumber = 1) {
        // 1. Ensure Wallet Connected
        let address = state.connectedAddress;
        if (!address) {
            address = await connectWallet(true);
            if (!address) return;
        }

        const injected = getInjectedProvider();
        if (!injected || !window.ethers) {
            toastr.error('Ethers.js or SafePal provider not ready.');
            return;
        }

        try {
            // Show loading overlay
            Swal.fire({
                title: 'Preparing Payment...',
                html: '<i class="fa-solid fa-spinner fa-spin fa-2x text-primary my-3"></i><p class="font-13 text-muted">Resolving blockchain beneficiary address...</p>',
                allowOutsideClick: false,
                showConfirmButton: false,
            });

            // 2. Fetch Assigned Payee from Laravel backend
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

            // 3. Prompt SafePal Transfer Request with full Payee details
            Swal.fire({
                title: 'Approve in SafePal',
                html: `
                    <div class="p-3 bg-light rounded-4 text-start mb-3 border">
                        <div class="d-flex align-items-center justify-content-between p-2 mb-2.5 rounded-3 bg-primary bg-opacity-10 border border-primary border-opacity-25">
                            <div>
                                <span class="text-muted font-10 text-uppercase d-block fw-semibold">Transfer Amount</span>
                                <h5 class="fw-bold text-primary mb-0 font-16">$${parseFloat(amountUsdt).toFixed(2)} <span class="font-12 fw-normal">USDT</span></h5>
                            </div>
                            <span class="badge bg-primary text-white font-11 px-2.5 py-1 rounded-pill">Slot ${slotNumber} Activation</span>
                        </div>
                        
                        <div class="p-2.5 bg-white rounded-3 border mb-2">
                            <div class="d-flex justify-content-between font-12 mb-1.5 pb-1 border-bottom border-light">
                                <span class="text-muted">Payee Name:</span>
                                <span class="fw-bold text-dark">${payeeName}</span>
                            </div>
                            <div class="d-flex justify-content-between font-12 mb-1.5 pb-1 border-bottom border-light">
                                <span class="text-muted">Payee Member ID:</span>
                                <span class="fw-bold font-monospace text-primary">${payeeMemberId}</span>
                            </div>
                            <div class="d-flex justify-content-between font-12 mb-1.5 pb-1 border-bottom border-light">
                                <span class="text-muted">Payee Wallet:</span>
                                <span class="fw-bold font-monospace text-dark">${payeeMasked}</span>
                            </div>
                            <div class="d-flex justify-content-between font-12">
                                <span class="text-muted">Network:</span>
                                <span class="fw-bold text-dark"><i class="fa-brands fa-ethereum text-warning me-1"></i> BSC (BEP-20)</span>
                            </div>
                        </div>
                    </div>
                    <div class="d-flex align-items-center justify-content-center gap-2 text-primary font-13 fw-semibold">
                        <i class="fa-solid fa-fingerprint fa-bounce fa-lg"></i>
                        <span>Please confirm on your SafePal device / app...</span>
                    </div>
                `,
                allowOutsideClick: false,
                showConfirmButton: false,
            });

            const provider = new ethers.BrowserProvider(injected);
            const signer = await provider.getSigner();
            const usdtContract = new ethers.Contract(usdtContractAddr, BEP20_ABI, signer);

            // Execute on-chain transfer
            const tx = await usdtContract.transfer(payeeAddress, amountWei);

            // 4. Transaction Broadcasted -> Show Tracking Spinner
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

            // Wait for 1 confirmation
            await tx.wait(1);

            // 5. Submit TxHash to Laravel Backend
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
                // Success Modal
                Swal.fire({
                    icon: 'success',
                    title: 'Slot Activated Successfully!',
                    html: `
                        <p class="text-muted font-13">${trackData.message}</p>
                        <div class="p-2.5 bg-success bg-opacity-10 border border-success border-opacity-25 rounded-3 mb-3">
                            <span class="text-success font-12 fw-bold"><i class="fa-solid fa-circle-check me-1"></i> Verified on BNB Smart Chain</span>
                        </div>
                    `,
                    confirmButtonText: 'Go to Dashboard',
                    confirmButtonColor: '#198754',
                }).then(() => {
                    window.location.reload();
                });
            } else {
                Swal.fire({
                    icon: 'warning',
                    title: 'Verification In Progress',
                    text: trackData.message || 'Payment broadcasted. Please click Track Payment if activation takes a few moments.',
                    confirmButtonText: 'Refresh Page'
                }).then(() => {
                    window.location.reload();
                });
            }

        } catch (err) {
            console.error('Payment error:', err);
            if (err.code === 'ACTION_REJECTED' || err.code === 4001) {
                Swal.fire({
                    icon: 'info',
                    title: 'Payment Cancelled',
                    text: 'You declined the transaction in SafePal.',
                    confirmButtonText: 'OK'
                });
            } else {
                Swal.fire({
                    icon: 'error',
                    title: 'Payment Failed',
                    text: err.reason || err.message || 'Failed to complete transaction.',
                });
            }
        }
    }

    // Auto-listen for account/network changes
    if (typeof window !== 'undefined') {
        const injected = getInjectedProvider();
        if (injected) {
            injected.on?.('accountsChanged', (accounts) => {
                if (accounts.length > 0) {
                    state.connectedAddress = accounts[0].toLowerCase();
                    updateUIWithAddress(state.connectedAddress);
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
        connectWallet,
        paySlotWithSafePal,
        getState: () => state,
    };
})();

window.NevoraWeb3 = NevoraWeb3;
