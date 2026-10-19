{{-- Switch the cumulative table's ranking between total and average, client-side.
     Rows need data-total / data-avg attributes and a .pos-cell for the position. --}}
<div class="btn-group btn-group-sm mb-3 no-print" role="group" aria-label="Rank by">
    <span class="btn btn-light disabled" style="opacity:1;">Rank by:</span>
    <button type="button" class="btn btn-primary rank-toggle-btn" data-rank="total">Cumulative Total</button>
    <button type="button" class="btn btn-outline-primary rank-toggle-btn" data-rank="avg">Cumulative Average</button>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    var tbody = document.querySelector('#cumulativeTable tbody');
    var buttons = document.querySelectorAll('.rank-toggle-btn');
    if (!tbody || !buttons.length) return;

    var rows = Array.prototype.slice.call(tbody.querySelectorAll('tr[data-total]'));
    if (!rows.length) return;

    function rankBy(mode) {
        var primary = mode === 'avg' ? 'avg' : 'total';
        var secondary = mode === 'avg' ? 'total' : 'avg';

        rows.slice().sort(function (a, b) {
            return (parseFloat(b.dataset[primary]) - parseFloat(a.dataset[primary])) ||
                   (parseFloat(b.dataset[secondary]) - parseFloat(a.dataset[secondary]));
        }).forEach(function (row, i) {
            row.querySelector('.pos-cell').textContent = i + 1;
            tbody.appendChild(row);
        });

        buttons.forEach(function (btn) {
            var active = btn.dataset.rank === mode;
            btn.classList.toggle('btn-primary', active);
            btn.classList.toggle('btn-outline-primary', !active);
        });

        try { localStorage.setItem('cumulativeRankMode', mode); } catch (e) {}
    }

    buttons.forEach(function (btn) {
        btn.addEventListener('click', function () { rankBy(btn.dataset.rank); });
    });

    var saved = null;
    try { saved = localStorage.getItem('cumulativeRankMode'); } catch (e) {}
    if (saved === 'avg') rankBy('avg');
});
</script>
