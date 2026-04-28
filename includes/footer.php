<?php
// ============================================================
// includes/footer.php — Pied de page commun
// ============================================================
?>
<footer class="app-footer">
  <span>SuperMarché POS &copy; <?= date('Y') ?> — Université Protestante au Congo</span>
  <span>Développé en PHP Procédural &middot; Fichiers JSON</span>
</footer>
<div id="toast"></div>
<script>
function showToast(msg, type) {
  const t = document.getElementById('toast');
  t.textContent = msg;
  t.className = 'toast show ' + (type || '');
  setTimeout(() => t.className = 'toast', 3000);
}
</script>
</body>
</html>
