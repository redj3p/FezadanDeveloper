<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <title>FEZADAN | PORTFOLYO YÖNETİMİ</title>
    <link rel="icon" type="image/x-icon" href="/cdn/dark-favicon.ico">
    <link rel="icon" type="image/png" sizes="32x32" href="/cdn/dark-favicon-32x32.png">
    <link rel="icon" type="image/png" sizes="16x16" href="/cdn/dark-favicon-16x16.png">
    <link rel="apple-touch-icon" href="/cdn/dark-apple-touch-icon.png">
    <link rel="stylesheet" href="/assets/css/yonetim.css">
    <link rel="stylesheet" href="/assets/css/fonts.css">
    <style>
        :root {
            --bg-paper: #FEF9E1;
            --bg-secondary: #E5D0AC;
            --text-main: #6D2323;
            --text-accent: #A31D1D;
            --line-color: #6D2323;
        }
        body {
            background-color: var(--bg-paper);
            color: var(--text-main);
            font-family: 'Space Grotesk', sans-serif;
            overflow-x: hidden;
        }
        .font-syne { font-family: 'Syne', sans-serif; }
        .font-mono { font-family: 'JetBrains Mono', monospace; }
        
        .grid-bg {
            background-image: linear-gradient(var(--line-color) 1px, transparent 1px), linear-gradient(90deg, var(--line-color) 1px, transparent 1px);
            background-size: 40px 40px;
            opacity: 0.05;
            pointer-events: none;
        }
        .nav-item { position: relative; transition: all 0.3s; z-index: 1; }
        .nav-item::before { content: ''; position: absolute; left: 0; top: 0; bottom: 0; width: 0; background: var(--text-main); transition: width 0.3s; z-index: -1; }
        .nav-item:hover::before { width: 100%; }
        .nav-item:hover { color: var(--bg-paper); padding-left: 1.5rem; }
        .nav-item.active { background: var(--text-main); color: var(--bg-paper); }

        .brutalist-input {
            width: 100%;
            background: transparent;
            border-bottom: 2px solid var(--line-color);
            padding: 10px;
            font-family: 'Space Grotesk', sans-serif;
            outline: none;
            transition: 0.3s;
            color: var(--text-main);
        }
        .brutalist-input:focus {
            background: rgba(109,35,35,0.05);
        }
        [data-theme="dark"] .brutalist-input:focus {
            background: rgba(229,208,172,0.05);
        }

        .custom-scrollbar::-webkit-scrollbar { width: 6px; }
        .custom-scrollbar::-webkit-scrollbar-track { background: transparent; border-left: 1px dashed rgba(109,35,35,0.2); }
        .custom-scrollbar::-webkit-scrollbar-thumb { background: rgba(109,35,35,0.5); border-radius: 0px; }
        .custom-scrollbar::-webkit-scrollbar-thumb:hover { background: rgba(109,35,35,1); }
    </style>
</head>
<body class="flex h-screen w-full overflow-hidden relative">

    <div class="grid-bg fixed inset-0 z-0"></div>
    
    <?php include __DIR__ . '/_side_panel.php'; ?>

    <div class="flex flex-col xl:flex-row gap-8 h-full pb-6 w-full">
            
        <!-- Sol Kısım: Yeni Öge Ekleme -->
        <div class="w-full xl:w-5/12 flex-shrink-0">
            <h3 class="font-syne text-xl font-bold uppercase mb-4 flex items-center gap-2 text-[var(--text-main)]">
                <span class="w-3 h-3 bg-[#6D2323]"></span> Yeni Öge Ekle
            </h3>
            
            <form action="/tr/furkan/store" method="POST" enctype="multipart/form-data" class="border-2 border-[var(--text-main)] p-6 shadow-[8px_8px_0px_#A31D1D] bg-[var(--bg-paper)] space-y-4 max-h-[calc(100vh-170px)] overflow-y-auto custom-scrollbar">
                <?= Csrf::field() ?>
                
                <div>
                    <label class="block font-mono text-[10px] uppercase opacity-60 mb-1">Görsel Seç (Original En Yüksek Kalite)*</label>
                    <input type="file" name="image" accept="image/*" class="w-full text-xs font-mono border-2 border-dashed border-[var(--line-color)] p-3 cursor-pointer" required>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block font-mono text-[10px] uppercase opacity-60 mb-1">Başlık (TR)*</label>
                        <input type="text" name="title_tr" class="brutalist-input font-bold text-sm" placeholder="ÖRN: Dolunay Altında" required autocomplete="off">
                    </div>
                    <div>
                        <label class="block font-mono text-[10px] uppercase opacity-60 mb-1">Başlık (EN)</label>
                        <input type="text" name="title_en" class="brutalist-input font-bold text-sm" placeholder="ÖRN: Under the Full Moon" autocomplete="off">
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block font-mono text-[10px] uppercase opacity-60 mb-1">Açıklama (TR)</label>
                        <textarea name="description_tr" rows="3" class="brutalist-input text-xs" placeholder="Türkçe açıklama yazın..." autocomplete="off"></textarea>
                    </div>
                    <div>
                        <label class="block font-mono text-[10px] uppercase opacity-60 mb-1">Açıklama (EN)</label>
                        <textarea name="description_en" rows="3" class="brutalist-input text-xs" placeholder="English description here..." autocomplete="off"></textarea>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block font-mono text-[10px] uppercase opacity-60 mb-1">Tür*</label>
                        <select name="type" class="brutalist-input text-xs" required>
                            <option value="photo">Fotoğraf (Photo)</option>
                            <option value="drawing">Çizim (Drawing)</option>
                        </select>
                    </div>
                    <div>
                        <label class="block font-mono text-[10px] uppercase opacity-60 mb-1">Sıra (Display Order)</label>
                        <input type="number" name="display_order" class="brutalist-input text-xs" value="0" required min="0">
                    </div>
                </div>

                <button type="submit" class="w-full py-3 bg-[#6D2323] text-[#FEF9E1] font-bold uppercase hover:bg-black transition-all">
                    YÜKLE VE KAYDET [+]
                </button>

                <!-- Bildirimler -->
                <?php if (isset($_SESSION['error'])): ?>
                    <div class="text-xs font-mono text-center text-red-600 font-bold bg-red-100 p-2 rounded">
                        ⚠ <?= $_SESSION['error']; unset($_SESSION['error']); ?>
                    </div>
                <?php endif; ?>
                <?php if (isset($_SESSION['success'])): ?>
                    <div class="text-xs font-mono text-center text-green-700 font-bold bg-green-100 p-2 rounded">
                        ✓ <?= $_SESSION['success']; unset($_SESSION['success']); ?>
                    </div>
                <?php endif; ?>
            </form>
        </div>

        <!-- Sağ Kısım: Mevcut Ögelerin Listesi -->
        <div class="w-full xl:w-7/12 flex flex-col h-full min-h-0">
            <div class="flex justify-between items-center mb-4 flex-shrink-0">
                <h3 class="font-syne text-xl font-bold uppercase flex items-center gap-2 text-[var(--text-main)]">
                    <span class="w-3 h-3 bg-[#6D2323]"></span> Portfolyo Havuzu
                </h3>
                <button type="button" id="saveOrderBtn" class="px-4 py-2 border-2 border-[var(--text-main)] text-[var(--text-main)] font-mono text-[10px] font-bold uppercase hover:bg-[var(--text-main)] hover:text-[var(--bg-paper)] transition-all opacity-50 cursor-not-allowed" disabled>
                    Sıralamayı Kaydet
                </button>
            </div>

            <div class="border-2 border-[var(--text-main)] bg-[var(--bg-paper)] overflow-y-auto custom-scrollbar flex-1 shadow-[8px_8px_0px_rgba(163,29,29,0.1)] h-[calc(100vh-170px)]">
                <table class="w-full text-left font-mono text-xs border-collapse">
                    <thead class="bg-[#6D2323] text-[#FEF9E1] uppercase sticky top-0 z-20">
                        <tr>
                            <th class="p-3 border-b border-[var(--text-main)] w-12 text-center">Taşı</th>
                            <th class="p-3 border-b border-[var(--text-main)] w-16">Görsel</th>
                            <th class="p-3 border-b border-[var(--text-main)]">Başlık (TR/EN)</th>
                            <th class="p-3 border-b border-[var(--text-main)] w-24">Tür</th>
                            <th class="p-3 border-b border-[var(--text-main)] w-24">Sıralama</th>
                            <th class="p-3 border-b border-[var(--text-main)] w-20 text-right">İşlem</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-[var(--text-main)]/10">
                        <?php if (!empty($items)): foreach ($items as $item): 
                            $thumbUrl = Upload::assetUrl($item['image_url']);
                        ?>
                        <tr class="hover:bg-[var(--bg-secondary)]/30 transition-colors draggable-row cursor-grab" draggable="true" data-id="<?= $item['id'] ?>">
                            <td class="p-3 text-center drag-handle select-none text-base opacity-40 hover:opacity-100 transition-opacity">☰</td>
                            <td class="p-3">
                                <a href="<?= $thumbUrl ?>" target="_blank">
                                    <img src="<?= $thumbUrl ?>" alt="" class="w-10 h-10 object-cover border border-[var(--line-color)] rounded pointer-events-none">
                                </a>
                            </td>
                            <td class="p-3">
                                <div class="font-bold text-[var(--text-accent)] text-sm">
                                    <?= htmlspecialchars($item['title_tr']) ?>
                                </div>
                                <?php if (!empty($item['title_en'])): ?>
                                    <div class="opacity-50 text-[10px]">
                                        <?= htmlspecialchars($item['title_en']) ?>
                                    </div>
                                <?php endif; ?>
                            </td>
                            <td class="p-3 font-bold uppercase text-[10px]">
                                <span class="px-2 py-1 rounded bg-[var(--text-main)]/10 text-[var(--text-main)]">
                                    <?= $item['type'] === 'drawing' ? 'Çizim' : 'Fotoğraf' ?>
                                </span>
                            </td>
                            <td class="p-3">
                                <input type="number" 
                                       class="w-16 p-1 border border-[var(--line-color)] bg-transparent text-center order-input" 
                                       data-id="<?= $item['id'] ?>" 
                                       value="<?= $item['display_order'] ?>" 
                                       min="0">
                            </td>
                            <td class="p-3 text-right">
                                <form method="POST" action="/tr/furkan/delete"
                                      onsubmit="return confirm('Bu portfolyo ögesini silmek istediğinize emin misiniz? Resim R2 sunucusundan da tamamen silinecektir.');"
                                      class="inline">
                                    <?= Csrf::field() ?>
                                    <input type="hidden" name="id" value="<?= $item['id'] ?>">
                                    <button type="submit" class="text-[var(--text-accent)] hover:bg-[var(--text-accent)] hover:text-white px-2 py-1 transition-colors font-bold">
                                        [SİL]
                                    </button>
                                </form>
                            </td>
                        </tr>
                        <?php endforeach; else: ?>
                            <tr><td colspan="6" class="p-8 text-center opacity-50 uppercase font-mono italic">// Henüz portfolyo ögesi eklenmemiş.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

    </div>
    
</div> </main>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const saveOrderBtn = document.getElementById('saveOrderBtn');
    const orderInputs = document.querySelectorAll('.order-input');
    const tbody = document.querySelector('tbody');
    let draggedRow = null;
    
    // Enable save button when any input changes manually
    orderInputs.forEach(input => {
        input.addEventListener('change', () => {
            enableSaveButton();
        });
    });

    function enableSaveButton() {
        saveOrderBtn.disabled = false;
        saveOrderBtn.style.opacity = '1';
        saveOrderBtn.classList.remove('cursor-not-allowed');
    }

    // Drag and Drop implementation for table rows
    tbody.addEventListener('dragstart', (e) => {
        const row = e.target.closest('tr');
        if (row && row.classList.contains('draggable-row')) {
            draggedRow = row;
            row.classList.add('opacity-40', 'bg-[var(--bg-secondary)]/50');
            e.dataTransfer.effectAllowed = 'move';
        }
    });

    tbody.addEventListener('dragend', (e) => {
        if (draggedRow) {
            draggedRow.classList.remove('opacity-40', 'bg-[var(--bg-secondary)]/50');
            draggedRow = null;
        }
    });

    tbody.addEventListener('dragover', (e) => {
        e.preventDefault();
        const targetRow = e.target.closest('tr');
        if (targetRow && targetRow !== draggedRow && targetRow.classList.contains('draggable-row') && targetRow.parentElement === tbody) {
            const rect = targetRow.getBoundingClientRect();
            const next = (e.clientY - rect.top) / (rect.bottom - rect.top) > 0.5;
            tbody.insertBefore(draggedRow, next ? targetRow.nextSibling : targetRow);
            
            recalculateDisplayOrders();
        }
    });

    function recalculateDisplayOrders() {
        const rows = tbody.querySelectorAll('.draggable-row');
        rows.forEach((row, index) => {
            const input = row.querySelector('.order-input');
            if (input) {
                input.value = (index + 1) * 10;
            }
        });
        enableSaveButton();
    }

    saveOrderBtn.addEventListener('click', () => {
        const payload = new URLSearchParams();
        const freshInputs = document.querySelectorAll('.order-input');
        
        // Gather all inputs
        freshInputs.forEach(input => {
            const id = input.getAttribute('data-id');
            const val = input.value;
            payload.append(`orders[${id}]`, val);
        });

        // CSRF Token
        const csrfToken = document.querySelector('input[name="_csrf"]').value;
        payload.append('_csrf', csrfToken);

        saveOrderBtn.disabled = true;
        saveOrderBtn.textContent = 'KAYDEDİLİYOR...';

        fetch('/tr/furkan/reorder', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: payload.toString()
        })
        .then(response => {
            if (!response.ok) {
                throw new Error('Sıralama güncellenemedi.');
            }
            return response.json();
        })
        .then(data => {
            if (data.success) {
                alert('Sıralama başarıyla güncellendi!');
                location.reload();
            } else {
                alert('Hata: ' + (data.error || 'Bilinmeyen bir hata oluştu.'));
                saveOrderBtn.disabled = false;
                saveOrderBtn.textContent = 'Sıralamayı Kaydet';
            }
        })
        .catch(err => {
            alert('Ağ hatası: ' + err.message);
            saveOrderBtn.disabled = false;
            saveOrderBtn.textContent = 'Sıralamayı Kaydet';
        });
    });
});
</script>

</body>
</html>
