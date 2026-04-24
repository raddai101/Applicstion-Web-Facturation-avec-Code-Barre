<?php include 'includes/header.php'; ?>

<style>
.page-test-scanner .card {
    margin-bottom: 20px;
}

.scanner-info {
    background: #FFF3E0;
    border-left: 4px solid #FF9800;
    padding: 15px;
    border-radius: 4px;
    margin-bottom: 15px;
}

.scanner-info strong {
    color: #E65100;
}

.test-codes {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 10px;
    margin-top: 15px;
}

.test-code-btn {
    background: #4CAF50;
    color: white;
    border: none;
    padding: 12px;
    border-radius: 6px;
    cursor: pointer;
    font-weight: bold;
    transition: all 0.3s;
}

.test-code-btn:hover {
    background: #45a049;
    transform: translateY(-2px);
}

.btn-manual {
    background: #3498DB;
}

.btn-manual:hover {
    background: #2980B9;
}

#preview {
    width: 100%;
    max-width: 500px;
    height: 400px;
    border-radius: 8px;
    margin: 0 auto;
    display: block;
}

#scanner-canvas {
    display: none;
}
</style>

<div class="page-test-scanner">

<div class="card">
    <h2>🔍 Test du Scanner de Codes-barres</h2>
    <p>Cette page permet de tester le fonctionnement du scanner en temps réel.</p>
</div>

<!-- Instructions -->
<div class="scanner-info">
    <strong>ℹ️ Instructions :</strong><br>
    ✓ Le flux vidéo s'affiche dans la zone de scan<br>
    ✓ Placez le code-barres devant la caméra<br>
    ✓ Quand un code est détecté : bip sonore + message vert<br>
    ✓ Vous pouvez aussi entrer un code manuellement<br>
</div>

<!-- Scanner -->
<div class="card" style="text-align: center;">
    <h3>📹 Scanner de Codes-barres</h3>
    <video id="preview" style="border: 3px solid #3498DB;"></video>
</div>

<!-- Boutons de contrôle -->
<div class="card">
    <h3>🎮 Contrôles</h3>
    <div class="test-codes">
        <button type="button" onclick="manualScanInput()" class="test-code-btn btn-manual">
            📝 Saisie manuelle
        </button>
    </div>
</div>

<!-- Codes de test -->
<div class="card">
    <h3>🧪 Codes de Test</h3>
    <p>Cliquez sur un bouton pour tester avec ce code :</p>
    <div class="test-codes">
        <button type="button" onclick="testCode('3017620422003')" class="test-code-btn">
            Vain amour<br><small>3017620422003</small>
        </button>
        <button type="button" onclick="testCode('1234567890123')" class="test-code-btn">
            Pain<br><small>1234567890123</small>
        </button>
        <button type="button" onclick="testCode('9876543210987')" class="test-code-btn">
            Lait<br><small>9876543210987</small>
        </button>
        <button type="button" onclick="testCode('5556667778889')" class="test-code-btn">
            Riz<br><small>5556667778889</small>
        </button>
    </div>
</div>

<!-- Informations utiles -->
<div class="card">
    <h3>💡 Conseils</h3>
    <ul>
        <li><strong>Éclairage :</strong> Assure-toi que le code-barres est bien éclairé</li>
        <li><strong>Distance :</strong> Place le code-barres à 10-20 cm de la caméra</li>
        <li><strong>Angle :</strong> Positionne le code-barres perpendiculairement à la caméra</li>
        <li><strong>Vitesse :</strong> Scanne lentement et régulièrement</li>
        <li><strong>Fallback :</strong> Si la caméra ne fonctionne pas, utilise la saisie manuelle</li>
    </ul>
</div>

<script src="/facturation/assets/js/scanner.js"></script>
<script>
function testCode(code) {
    console.log('Code test:', code);
    showStatus('Code de test: ' + code, 'detected');
    processBarcode(code);
}
</script>

</div>

<?php include 'includes/footer.php'; ?></content>
<parameter name="filePath">c:\xampp\htdocs\facturation\test-scanner.php