import { EthereumProvider } from '@walletconnect/ethereum-provider';
import QRCode from 'qrcode';

if (typeof window !== 'undefined') {
    window.WalletConnectEthereumProvider = EthereumProvider;
    window.NevoraQRCode = QRCode;
    if (!window.QRCode) {
        window.QRCode = QRCode;
    }
}

export { EthereumProvider, QRCode };
