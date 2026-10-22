<!-- Crypto Animated Loader Start -->
<div id="loading">
  <div class="loader crypto-loader">
    <div class="crypto-loader-wrapper">
      <div class="crypto-orbit-stage">
        <!-- Outer Orbit Ring (Tilted Clockwise) -->
        <div class="crypto-ring crypto-ring-outer"></div>

        <!-- Middle Counter-Rotating Ring -->
        <div class="crypto-ring crypto-ring-middle"></div>

        <!-- Inner Ring with Orbiting Blockchain Data Node -->
        <div class="crypto-ring crypto-ring-inner">
          <div class="crypto-node"></div>
        </div>

        <!-- Central 3D Spinning Crypto Emblem -->
        <div class="crypto-coin">
          <div class="crypto-coin-face crypto-coin-front">
            <img src="{{ asset('assets/images/logo-icon.png') }}" alt="NEVORA" style="width: 40px; height: 40px; object-fit: contain;">
          </div>
          <div class="crypto-coin-face crypto-coin-back">
            <img src="{{ asset('assets/images/logo-icon.png') }}" alt="NEVORA" style="width: 40px; height: 40px; object-fit: contain;">
          </div>
        </div>

        <!-- Radial Crypto Energy Glow -->
        <div class="crypto-core-glow"></div>
      </div>

      <!-- Crypto Platform Branding & Live Sync Status -->
      <div class="crypto-loader-meta">
        <h6 class="crypto-loader-title">NEVORA</h6>
        <div class="crypto-sync-badge">
          <span class="crypto-live-dot"></span>
          <span class="crypto-sync-text">SYNCHRONIZING ON-CHAIN</span>
        </div>
      </div>
    </div>
  </div>
</div>
<!-- Crypto Animated Loader END -->

<style>
/* ============================================================
   CRYPTOCURRENCY ANIMATED 3D LOADER
   ============================================================ */
#loading {
    position: fixed !important;
    top: 0;
    left: 0;
    width: 100vw;
    height: 100vh;
    z-index: 999999 !important;
    display: flex;
    align-items: center;
    justify-content: center;
    background: #f8fafc;
    transition: opacity 0.45s ease, visibility 0.45s ease;
}

[data-bs-theme="dark"] #loading {
    background: #090d16 !important;
}

.crypto-loader {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 100%;
    height: 100%;
}

.crypto-loader-wrapper {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    gap: 24px;
    user-select: none;
}

.crypto-orbit-stage {
    position: relative;
    width: 130px;
    height: 130px;
    display: flex;
    align-items: center;
    justify-content: center;
    perspective: 800px;
}

/* Orbit Rings */
.crypto-ring {
    position: absolute;
    border-radius: 50%;
    pointer-events: none;
}

.crypto-ring-outer {
    width: 126px;
    height: 126px;
    border: 2px dashed rgba(59, 130, 246, 0.4);
    box-shadow: 0 0 20px rgba(59, 130, 246, 0.15);
    animation: cryptoOrbitClockwise 6s linear infinite;
}

.crypto-ring-middle {
    width: 100px;
    height: 100px;
    border: 2px solid transparent;
    border-top: 2.5px solid #3b82f6;
    border-right: 2.5px solid #8b5cf6;
    border-radius: 50%;
    transform: rotateX(65deg) rotateY(20deg);
    animation: cryptoOrbitCounter 3.2s linear infinite;
    filter: drop-shadow(0 0 6px rgba(59, 130, 246, 0.5));
}

.crypto-ring-inner {
    width: 76px;
    height: 76px;
    border: 1.5px solid rgba(139, 92, 246, 0.35);
    border-radius: 50%;
    transform: rotateX(60deg) rotateY(-20deg);
    animation: cryptoOrbitClockwise 2.6s linear infinite;
}

/* Orbiting Blockchain Node */
.crypto-node {
    position: absolute;
    top: -4px;
    left: calc(50% - 4px);
    width: 8px;
    height: 8px;
    background: #00f2fe;
    border-radius: 50%;
    box-shadow: 0 0 10px #00f2fe, 0 0 18px #3b82f6;
}

/* Central 3D Flipping Coin */
.crypto-coin {
    position: relative;
    width: 56px;
    height: 56px;
    transform-style: preserve-3d;
    animation: cryptoCoin3D 3.2s cubic-bezier(0.4, 0, 0.2, 1) infinite;
    z-index: 2;
}

.crypto-coin-face {
    position: absolute;
    width: 100%;
    height: 100%;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    background: linear-gradient(145deg, #ffffff, #eef2ff);
    border: 2px solid #3b82f6;
    box-shadow: 0 6px 18px rgba(37, 99, 235, 0.25), inset 0 0 10px rgba(59, 130, 246, 0.2);
    backface-visibility: hidden;
    -webkit-backface-visibility: hidden;
}

[data-bs-theme="dark"] .crypto-coin-face {
    background: linear-gradient(145deg, #1e293b, #0f172a);
    border-color: #60a5fa;
    box-shadow: 0 0 20px rgba(59, 130, 246, 0.4), inset 0 0 12px rgba(139, 92, 246, 0.35);
}

.crypto-coin-back {
    transform: rotateY(180deg);
}

/* Core Energy Pulse Glow */
.crypto-core-glow {
    position: absolute;
    width: 80px;
    height: 80px;
    border-radius: 50%;
    background: radial-gradient(circle, rgba(59, 130, 246, 0.25) 0%, rgba(139, 92, 246, 0) 70%);
    animation: cryptoPulse 2s ease-in-out infinite alternate;
    pointer-events: none;
    z-index: 1;
}

/* Loader Title & Network Status */
.crypto-loader-meta {
    text-align: center;
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 8px;
}

.crypto-loader-title {
    margin: 0;
    font-size: 15px;
    font-weight: 800;
    letter-spacing: 3.5px;
    background: linear-gradient(90deg, #1d4ed8, #06b6d4, #7c3aed, #1d4ed8);
    background-size: 250% auto;
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    animation: cryptoShimmer 3s linear infinite;
}

[data-bs-theme="dark"] .crypto-loader-title {
    background: linear-gradient(90deg, #60a5fa, #38bdf8, #c084fc, #60a5fa);
    background-size: 250% auto;
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
}

.crypto-sync-badge {
    display: inline-flex;
    align-items: center;
    gap: 7px;
    padding: 4px 12px;
    border-radius: 20px;
    background: rgba(59, 130, 246, 0.08);
    border: 1px solid rgba(59, 130, 246, 0.2);
}

[data-bs-theme="dark"] .crypto-sync-badge {
    background: rgba(255, 255, 255, 0.04);
    border-color: rgba(255, 255, 255, 0.1);
}

.crypto-sync-text {
    font-size: 9.5px;
    font-weight: 700;
    letter-spacing: 0.8px;
    color: #2563eb;
}

[data-bs-theme="dark"] .crypto-sync-text {
    color: #93c5fd;
}

.crypto-live-dot {
    width: 6.5px;
    height: 6.5px;
    border-radius: 50%;
    background: #10b981;
    box-shadow: 0 0 8px #10b981;
    animation: cryptoLivePulse 1.4s infinite;
}

/* Keyframes */
@keyframes cryptoCoin3D {
    0% {
        transform: rotateY(0deg) rotateX(10deg);
    }
    50% {
        transform: rotateY(180deg) rotateX(-10deg);
    }
    100% {
        transform: rotateY(360deg) rotateX(10deg);
    }
}

@keyframes cryptoOrbitClockwise {
    from { transform: rotate(0deg); }
    to { transform: rotate(360deg); }
}

@keyframes cryptoOrbitCounter {
    from { transform: rotateX(65deg) rotateY(20deg) rotateZ(360deg); }
    to { transform: rotateX(65deg) rotateY(20deg) rotateZ(0deg); }
}

@keyframes cryptoPulse {
    0% {
        transform: scale(0.85);
        opacity: 0.35;
    }
    100% {
        transform: scale(1.3);
        opacity: 0.85;
    }
}

@keyframes cryptoLivePulse {
    0%, 100% {
        transform: scale(1);
        opacity: 1;
        box-shadow: 0 0 0 0 rgba(16, 185, 129, 0.6);
    }
    50% {
        transform: scale(0.85);
        opacity: 0.7;
        box-shadow: 0 0 0 4px rgba(16, 185, 129, 0);
    }
}

@keyframes cryptoShimmer {
    to { background-position: 250% center; }
}
</style>
