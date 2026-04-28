<?php
// ============================================================
// index.php — Page principale : Caisse / Facturation
// ============================================================
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/includes/fonctions-auth.php';
require_once __DIR__ . '/includes/fonctions-produits.php';
require_once __DIR__ . '/includes/fonctions-factures.php';
require_once __DIR__ . '/auth/session.php';

verifier_role(['caissier','manager','superadmin']);

$page_title = 'Caisse — SuperMarché POS';
$user       = utilisateur_courant();
$flash_ok   = flash('flash_ok');
$flash_err  = flash('flash_error');

// ── Traitement AJAX : rechercher produit par code-barres ──
if (isset($_GET['ajax']) && $_GET['ajax'] === 'scan') {
    header('Content-Type: application/json');
    $code = trim($_GET['code'] ?? '');
    if (!$code) { echo json_encode(['found'=>false,'error'=>'Code vide']); exit; }
    $prod = chercher_produit_par_code($code);
    if ($prod) {
        echo json_encode(['found'=>true,'produit'=>$prod]);
    } else {
        echo json_encode(['found'=>false,'code'=>$code]);
    }
    exit;
}

// ── Traitement POST : valider la facture ──
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['valider_facture'])) {
    $articles_json = $_POST['articles_json'] ?? '[]';
    $articles      = json_decode($articles_json, true);
    if (!empty($articles)) {
        $facture = creer_facture($articles, $user['identifiant']);
        $_SESSION['flash_ok'] = 'Facture ' . $facture['id_facture'] . ' validée avec succès !';
        header('Location: ' . BASE_URL . '/modules/facturation/afficher-facture.php?id=' . urlencode($facture['id_facture']));
        exit;
    }
    $flash_err = 'Impossible de valider : aucun article dans la facture.';
}

require_once __DIR__ . '/includes/header.php';
?>

<div class="main-layout">
  <!-- ════ COLONNE GAUCHE ════ -->
  <div class="left-panel" id="lp">

    <?php if ($flash_ok):  ?><div class="alert alert-green au">✓ <?= htmlspecialchars($flash_ok) ?></div><?php endif; ?>
    <?php if ($flash_err): ?><div class="alert alert-red au">✕ <?= htmlspecialchars($flash_err) ?></div><?php endif; ?>

    <!-- ── SCANNER ── -->
    <div class="card au">
      <div class="card-header">
        <div class="title-group">
          <div class="section-icon icon-blue">📷</div>
          <div>
            <div class="section-label">Scanner ZXing — Multi-format</div>
            <div class="section-sub">EAN-13 · EAN-8 · CODE-128 · QR · UPC · DATA-MATRIX</div>
          </div>
        </div>
        <div style="display:flex;gap:8px;align-items:center">
          <div class="mode-tabs">
            <button class="mode-tab active" data-mode="cam">Caméra</button>
            <button class="mode-tab"        data-mode="man">Manuel</button>
          </div>
          <span class="badge badge-blue" id="scan-badge">INACTIF</span>
        </div>
      </div>
      <div class="card-body">
        <div class="scanner-body">
          <!-- Vidéo -->
          <div class="video-wrap" id="vwrap">
            <video id="preview" autoplay playsinline muted></video>
            <div class="scan-overlay">
              <div class="sf">
                <div class="sc-tr"></div>
                <div class="sc-bl"></div>
              </div>
              <div class="scan-line" id="sline"></div>
            </div>
            <div class="vid-label" id="vlabel">Caméra inactive</div>
          </div>
          <!-- Contrôles -->
          <div class="scanner-right">
            <button class="btn btn-primary btn-w100" id="btn-start">▶ Démarrer le scanner</button>
            <button class="btn btn-danger  btn-w100" id="btn-stop"  style="display:none">⏹ Arrêter</button>
            <div class="manual-row" id="man-row" style="display:none">
              <input type="text" id="man-in" placeholder="Saisir code-barres..." maxlength="25">
              <button id="btn-manual-ok">OK</button>
            </div>
            <div class="result-box" id="rbox">En attente de scan...</div>
            <div class="prog-bar"><div class="prog-fill" id="pfill"></div></div>
            <div class="info-row">
              <span class="info-chip">ZXing Latest</span>
              <span class="info-chip" id="fps-chip">0 fps</span>
              <span class="info-chip" id="cam-chip">— cam</span>
              <span class="info-chip" id="cnt-chip">0 scans</span>
            </div>
            <div class="fmt-row">
              <span class="fmt-badge">EAN-13</span><span class="fmt-badge">EAN-8</span>
              <span class="fmt-badge">CODE-128</span><span class="fmt-badge">QR</span>
              <span class="fmt-badge">UPC-A</span><span class="fmt-badge">ITF</span>
              <span class="fmt-badge">PDF-417</span><span class="fmt-badge">DATA-MTX</span>
            </div>
            <button class="btn btn-secondary btn-w100" id="btn-switch" style="display:none">🔄 Changer caméra</button>
          </div>
        </div>
        <div class="stock-alert" id="salert">⚠️ <span id="salert-msg"></span></div>
      </div>
    </div>

    <!-- ── FICHE PRODUIT ── -->
    <div class="card au" id="prod-card">
      <div class="card-header">
        <div class="title-group">
          <span style="font-size:18px">📦</span>
          <div>
            <div class="section-label" id="pf-title">Produit détecté</div>
            <div class="section-sub"   id="pf-sub">En attente de scan...</div>
          </div>
        </div>
        <span class="badge" id="pf-badge" style="display:none"></span>
      </div>
      <div class="card-body">
        <div class="form-grid">
          <div class="form-field full">
            <label>Code-barres</label>
            <div class="bc-display" id="bc-disp">—</div>
          </div>
          <div class="form-field full">
            <label>Nom du produit</label>
            <input type="text" id="f-nom" placeholder="Nom du produit" readonly>
          </div>
          <div class="form-field">
            <label>Prix unitaire HT (CDF)</label>
            <input type="number" id="f-prix" placeholder="0" readonly>
          </div>
          <div class="form-field">
            <label>Stock disponible</label>
            <input type="number" id="f-stock" placeholder="0" readonly>
          </div>
          <div class="form-field">
            <label>Date d'expiration</label>
            <input type="date" id="f-exp" readonly>
          </div>
          <div class="form-field">
            <label>Quantité à facturer</label>
            <input type="number" id="f-qty" placeholder="1" min="1" value="1">
          </div>
          <div class="form-field full">
            <div class="btn-row">
              <button class="btn btn-success" id="btn-add" onclick="cartAdd()" disabled style="flex:1">
                ✓ Ajouter à la facture
              </button>
              <?php if (a_role('manager')): ?>
              <button class="btn btn-secondary" id="btn-register" onclick="goRegister()" style="display:none">
                + Enregistrer produit
              </button>
              <?php endif; ?>
              <button class="btn btn-secondary" onclick="clearForm()" style="padding:9px 12px">✕</button>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- ── TABLE ARTICLES ── -->
    <div class="card au">
      <div class="card-header">
        <div class="title-group">
          <span style="font-size:18px">🛒</span>
          <div>
            <div class="section-label">Articles de la facture</div>
            <div class="section-sub" id="items-sub">Aucun article</div>
          </div>
        </div>
        <span class="badge badge-purple" id="items-badge">0 <?= DEVISE ?></span>
      </div>
      <table class="items-table">
        <thead>
          <tr>
            <th>Produit</th>
            <th>Prix HT</th>
            <th>Qté</th>
            <th>Sous-total</th>
            <th></th>
          </tr>
        </thead>
        <tbody id="tbod">
          <tr id="empty-tr">
            <td colspan="5">
              <div class="empty-state">
                <div class="empty-icon">🛒</div>
                <div class="empty-title">Aucun article</div>
                <div style="font-size:11px;color:var(--text3)">Scannez un code-barres pour commencer</div>
              </div>
            </td>
          </tr>
        </tbody>
      </table>
    </div>

  </div><!-- /left-panel -->

  <!-- ════ TICKET CAISSE (droite) ════ -->
  <div class="right-panel">
    <div class="rp-header">
      <div class="rp-id" id="rp-id">FAC — —</div>
      <div class="rp-title">Facture en cours</div>
      <div class="rp-meta">
        <div class="meta-chip" id="rp-date"><?= date('d/m/Y H:i') ?></div>
        <div class="meta-chip"><?= htmlspecialchars($user['nom_complet']) ?></div>
        <span class="badge badge-amber pulse" id="rp-status">EN COURS</span>
      </div>
    </div>

    <div class="rp-body" id="rp-body">
      <div class="empty-state" id="rp-empty">
        <div class="empty-icon">🧾</div>
        <div class="empty-title" style="font-size:12px">Facture vide</div>
        <div style="font-size:10px;color:var(--text3)">Les articles s'affichent ici</div>
      </div>
    </div>

    <hr class="rp-divider">

    <div class="totals">
      <div class="tr-row tr-ht">
        <span class="tr-label">Total HT</span>
        <span class="tr-val" id="t-ht">0 <?= DEVISE ?></span>
      </div>
      <div class="tr-row tr-tva">
        <span class="tr-label">TVA (<?= (TVA_TAUX * 100) ?>%)</span>
        <span class="tr-val" id="t-tva">0 <?= DEVISE ?></span>
      </div>
      <div class="total-big">
        <span class="tb-label">NET À PAYER</span>
        <span class="tb-val" id="t-ttc">0 <?= DEVISE ?></span>
      </div>
    </div>

    <!-- Formulaire validation (POST vers PHP) -->
    <form id="form-facture" method="POST" action="<?= BASE_URL ?>/index.php">
      <input type="hidden" name="valider_facture" value="1">
      <input type="hidden" name="articles_json" id="articles_json" value="[]">
      <div class="rp-footer">
        <button type="button" class="btn-clr" onclick="cartClear()">🗑</button>
        <button type="submit" class="btn-validate" id="btn-val" disabled>✓ Valider la facture</button>
      </div>
    </form>
  </div>

</div><!-- /main-layout -->

<?php require_once __DIR__ . '/includes/footer.php'; ?>

<!-- ══ ZXing CDN ══ -->
<script src="https://unpkg.com/@zxing/library@latest/umd/index.min.js"></script>
<!-- ══ Scanner module ══ -->
<script src="<?= BASE_URL ?>/assets/js/scanner.js"></script>

<script>
/* ════════════════════════════════════════════════════════════
   Logique Caisse — index.php
   ════════════════════════════════════════════════════════════ */
var TVA   = <?= TVA_TAUX ?>;
var DEVISE = '<?= DEVISE ?>';
var BASE_URL = '<?= BASE_URL ?>';

var cart        = [];
var currentBC   = null;
var currentProd = null;
var invNum      = <?= count(charger_factures()) + 1 ?>;

/* ── Init scanner ── */
document.addEventListener('DOMContentLoaded', function() {
  scannerInit(onBarcodeDetected);
  updateReceiptId();
});

/* ── Callback détection code-barres ── */
function onBarcodeDetected(code) {
  currentBC = code;
  document.getElementById('bc-disp').textContent = code;
  document.getElementById('salert').classList.remove('on');

  /* Appel AJAX PHP → chercher produit */
  fetch(BASE_URL + '/index.php?ajax=scan&code=' + encodeURIComponent(code))
    .then(function(r) { return r.json(); })
    .then(function(data) {
      if (data.found) {
        fillProductForm(data.produit);
      } else {
        showNewProduct(code);
      }
    })
    .catch(function() {
      scannerSetResult('Erreur réseau — vérifiez le serveur PHP', 'err');
    });
}

/* ── Remplir formulaire produit trouvé ── */
function fillProductForm(p) {
  currentProd = p;
  document.getElementById('pf-title').textContent  = 'Produit trouvé';
  document.getElementById('pf-sub').textContent    = p.nom;
  document.getElementById('f-nom').value   = p.nom;
  document.getElementById('f-prix').value  = p.prix_unitaire_ht;
  document.getElementById('f-stock').value = p.quantite_stock;
  document.getElementById('f-exp').value   = p.date_expiration;
  document.getElementById('f-qty').value   = 1;

  var badge = document.getElementById('pf-badge');
  badge.style.display = 'inline';
  badge.className     = 'badge badge-green';
  badge.textContent   = 'Stock: ' + p.quantite_stock;

  document.getElementById('btn-add').disabled      = false;
  document.getElementById('btn-add').style.display = 'flex';

  var btnReg = document.getElementById('btn-register');
  if (btnReg) btnReg.style.display = 'none';

  showToast('✓ ' + p.nom, 'ts');
}

/* ── Produit inconnu ── */
function showNewProduct(code) {
  currentProd = null;
  document.getElementById('pf-title').textContent = 'Produit inconnu';
  document.getElementById('pf-sub').textContent   = 'Code: ' + code;
  ['f-nom','f-prix','f-stock','f-exp'].forEach(function(id) {
    document.getElementById(id).value = '';
  });
  document.getElementById('f-qty').value = '';
  var badge = document.getElementById('pf-badge');
  badge.style.display = 'inline';
  badge.className     = 'badge badge-amber';
  badge.textContent   = 'NON ENREGISTRÉ';

  document.getElementById('btn-add').disabled = true;

  var btnReg = document.getElementById('btn-register');
  if (btnReg) btnReg.style.display = 'flex';

  showToast('Produit inconnu — veuillez l\'enregistrer', 'te');
}

/* ── Aller à l'enregistrement ── */
function goRegister() {
  if (currentBC) {
    window.location.href = BASE_URL + '/modules/produits/enregistrer.php?code=' + encodeURIComponent(currentBC);
  }
}

/* ── Effacer formulaire ── */
function clearForm() {
  currentBC = null; currentProd = null;
  document.getElementById('bc-disp').textContent     = '—';
  document.getElementById('pf-title').textContent    = 'Produit détecté';
  document.getElementById('pf-sub').textContent      = 'En attente de scan...';
  document.getElementById('pf-badge').style.display  = 'none';
  document.getElementById('btn-add').disabled        = true;
  ['f-nom','f-prix','f-stock','f-exp','f-qty'].forEach(function(id) {
    document.getElementById(id).value = '';
  });
  var btnReg = document.getElementById('btn-register');
  if (btnReg) btnReg.style.display = 'none';
  document.getElementById('salert').classList.remove('on');
  scannerSetResult('En attente de scan...', '');
}

/* ════ PANIER ════ */
function cartAdd() {
  if (!currentProd) return;
  var qty = parseInt(document.getElementById('f-qty').value) || 1;
  if (qty <= 0) { showToast('Quantité invalide', 'te'); return; }
  if (qty > currentProd.quantite_stock) {
    document.getElementById('salert').classList.add('on');
    document.getElementById('salert-msg').textContent =
      'Stock insuffisant : seulement ' + currentProd.quantite_stock + ' unité(s) disponible(s).';
    showToast('Stock insuffisant !', 'te');
    return;
  }

  var ex = cart.find(function(i){ return i.code_barre === currentProd.code_barre; });
  if (ex) {
    if (ex.quantite + qty > currentProd.quantite_stock) { showToast('Stock max dépassé', 'te'); return; }
    ex.quantite      += qty;
    ex.sous_total_ht  = ex.quantite * ex.prix_unitaire_ht;
  } else {
    cart.push({
      code_barre:      currentProd.code_barre,
      nom:             currentProd.nom,
      prix_unitaire_ht: currentProd.prix_unitaire_ht,
      quantite:        qty,
      sous_total_ht:   currentProd.prix_unitaire_ht * qty
    });
  }
  cartRender();
  clearForm();
  showToast(currentProd ? currentProd.nom + ' ajouté ×' + qty : 'Ajouté', 'ts');
}

function cartChangeQty(i, d) {
  var it = cart[i];
  var nq = it.quantite + d;
  if (nq < 1) { cart.splice(i,1); cartRender(); return; }
  it.quantite     = nq;
  it.sous_total_ht = nq * it.prix_unitaire_ht;
  cartRender();
}

function cartRemove(i) { cart.splice(i,1); cartRender(); }

function cartClear() {
  if (!cart.length) return;
  if (!confirm('Vider la facture en cours ?')) return;
  cart = []; cartRender(); showToast('Facture vidée', '');
}

/* ── Rendu table + ticket ── */
function cartRender() {
  var body    = document.getElementById('tbod');
  var emptyTr = document.getElementById('empty-tr');

  if (!cart.length) {
    body.innerHTML = '';
    body.appendChild(emptyTr);
    document.getElementById('items-sub').textContent    = 'Aucun article';
    document.getElementById('items-badge').textContent  = '0 ' + DEVISE;
    document.getElementById('btn-val').disabled         = true;
    document.getElementById('articles_json').value      = '[]';
    setTotals(0, 0, 0);
    renderTicket();
    return;
  }

  body.innerHTML = '';
  cart.forEach(function(it, i) {
    var tr = document.createElement('tr');
    tr.innerHTML =
      '<td><div class="pname">' + esc(it.nom) + '</div>' +
           '<div class="pcode">' + esc(it.code_barre) + '</div></td>' +
      '<td class="pcell">' + fmt(it.prix_unitaire_ht) + '</td>' +
      '<td><div class="qty-ctrl">' +
        '<button class="qb" onclick="cartChangeQty(' + i + ',-1)">−</button>' +
        '<span class="qv">' + it.quantite + '</span>' +
        '<button class="qb" onclick="cartChangeQty(' + i + ',1)">+</button>' +
      '</div></td>' +
      '<td class="pcell">' + fmt(it.sous_total_ht) + '</td>' +
      '<td><button class="rm-btn" onclick="cartRemove(' + i + ')">✕</button></td>';
    body.appendChild(tr);
  });

  var ht  = cart.reduce(function(s, i){ return s + i.sous_total_ht; }, 0);
  var tva = Math.round(ht * TVA);
  var ttc = ht + tva;

  document.getElementById('items-sub').textContent   = cart.length + ' article(s)';
  document.getElementById('items-badge').textContent = fmt(ttc);
  document.getElementById('btn-val').disabled        = false;
  document.getElementById('articles_json').value     = JSON.stringify(cart);

  setTotals(ht, tva, ttc);
  renderTicket();
  updateReceiptId();
}

function setTotals(ht, tva, ttc) {
  document.getElementById('t-ht').textContent  = fmt(ht);
  document.getElementById('t-tva').textContent = fmt(tva);
  document.getElementById('t-ttc').textContent = fmt(ttc);
}

function renderTicket() {
  var c = document.getElementById('rp-body');
  if (!cart.length) {
    c.innerHTML = '<div class="empty-state"><div class="empty-icon">🧾</div>' +
      '<div class="empty-title" style="font-size:12px">Facture vide</div></div>';
    return;
  }
  c.innerHTML = cart.map(function(it) {
    return '<div class="ri">' +
      '<div class="ri-top"><div class="ri-name">' + esc(it.nom) + '</div>' +
      '<div class="ri-amt">' + fmt(it.sous_total_ht) + '</div></div>' +
      '<div class="ri-bot">' + it.quantite + ' × ' + fmt(it.prix_unitaire_ht) + '/u</div>' +
      '</div>';
  }).join('');
}

function updateReceiptId() {
  var d = new Date();
  var ds = d.getFullYear().toString() +
           String(d.getMonth()+1).padStart(2,'0') +
           String(d.getDate()).padStart(2,'0');
  document.getElementById('rp-id').textContent =
    'FAC-' + ds + '-' + String(invNum).padStart(3,'0');
  document.getElementById('rp-date').textContent = d.toLocaleString('fr-FR');
}

/* ── Helpers ── */
function fmt(n)   { return Number(n).toLocaleString('fr-FR') + ' ' + DEVISE; }
function esc(s)   { var d=document.createElement('div'); d.textContent=s; return d.innerHTML; }
function showToast(m, c) {
  var t=document.getElementById('toast');
  t.textContent=m; t.className='toast show '+(c||'');
  setTimeout(function(){ t.className='toast'; }, 3000);
}
</script>
