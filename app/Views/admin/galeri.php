<?php require_once ROOT . '/app/Views/admin/layouts/header.php'; ?>

<div class="content">
    <div class="header-actions">
        <h1>Galeri Yönetimi</h1>
    </div>

    <?php if (isset($_GET['status']) && $_GET['status'] == 'refreshed'): ?>
        <div class="alert alert-success">Günün sanat eseri başarıyla yenilendi.</div>
    <?php endif; ?>
    <?php if (isset($_GET['status']) && $_GET['status'] == 'updated'): ?>
        <div class="alert alert-success">Eser açıklaması güncellendi.</div>
    <?php endif; ?>
    <?php if (isset($_GET['error']) && $_GET['error'] == 'refresh_failed'): ?>
        <div class="alert alert-danger">Eser yenilenirken bir hata oluştu. Tüm API kotaları dolmuş olabilir.</div>
    <?php endif; ?>

    <div style="display: flex; gap: 2rem; margin-bottom: 2rem; flex-wrap: wrap;">
        
        <!-- Günün Eseri Kartı -->
        <div style="flex: 1; min-width: 300px; background: #fff; padding: 20px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.05);">
            <div style="display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid #eee; padding-bottom: 10px; margin-bottom: 20px;">
                <h2 style="margin: 0; font-size: 1.2rem;">Günün Eseri (<?= date('d.m.Y') ?>)</h2>
                <form action="/admin/refreshDailyArt" method="POST" onsubmit="return confirm('Mevcut eseri silip API\'den yenisini çekmek istediğinize emin misiniz? Bu işlem geri alınamaz.');">
                    <button type="submit" class="btn btn-warning" style="font-size: 0.8rem; padding: 5px 10px;">Yenile (Force Fetch)</button>
                </form>
            </div>

            <?php if ($todayArt): ?>
                <div style="display: flex; gap: 20px;">
                    <div style="flex: 0 0 150px;">
                        <img src="<?= htmlspecialchars($todayArt['thumbnail_url'] ?: $todayArt['image_url']) ?>" style="width: 100%; height: auto; border-radius: 4px;" alt="Artwork">
                    </div>
                    <div style="flex: 1;">
                        <h3 style="margin: 0 0 5px 0;"><?= htmlspecialchars($todayArt['title']) ?></h3>
                        <p style="margin: 0 0 15px 0; color: #666; font-style: italic;"><?= htmlspecialchars($todayArt['artist']) ?></p>
                        <div style="font-size: 0.9rem; margin-bottom: 15px;">
                            <strong>Müze:</strong> <?= htmlspecialchars($todayArt['provider']) ?><br>
                            <strong>Kaynak:</strong> <span style="background: #eee; padding: 2px 6px; border-radius: 3px; font-size: 0.8rem;"><?= htmlspecialchars($todayArt['description_source']) ?></span>
                        </div>
                        <button onclick="document.getElementById('edit-modal-<?= $todayArt['id'] ?>').style.display='block'" class="btn btn-primary" style="padding: 5px 10px; font-size: 0.8rem;">Açıklamayı Düzenle</button>
                    </div>
                </div>
                
                <!-- Edit Modal -->
                <div id="edit-modal-<?= $todayArt['id'] ?>" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.5); z-index:9999; justify-content:center; align-items:center;">
                    <div style="background:#fff; padding:20px; border-radius:8px; width:90%; max-width:600px; max-height:90vh; overflow-y:auto; margin: 5vh auto;">
                        <h3 style="margin-top:0;">Açıklamayı Düzenle (<?= htmlspecialchars($todayArt['title']) ?>)</h3>
                        <form action="/admin/updateArtDescription" method="POST">
                            <input type="hidden" name="id" value="<?= $todayArt['id'] ?>">
                            <div class="form-group">
                                <label>Orijinal Açıklama (İngilizce)</label>
                                <textarea readonly class="form-control" style="background:#f9f9f9; height:100px;"><?= htmlspecialchars($todayArt['description_en']) ?></textarea>
                            </div>
                            <div class="form-group" style="margin-top:15px;">
                                <label>Türkçe Açıklama (Sitede Görünecek)</label>
                                <textarea name="description_tr" class="form-control" style="height:200px;" required><?= htmlspecialchars($todayArt['description_tr']) ?></textarea>
                            </div>
                            <div style="margin-top:20px; display:flex; justify-content:flex-end; gap:10px;">
                                <button type="button" class="btn" onclick="document.getElementById('edit-modal-<?= $todayArt['id'] ?>').style.display='none'">İptal</button>
                                <button type="submit" class="btn btn-primary">Kaydet</button>
                            </div>
                        </form>
                    </div>
                </div>

            <?php else: ?>
                <p style="color: #888; font-style: italic;">Bugün için henüz bir eser çekilmedi. Siteye bir ziyaretçi girdiğinde veya yukarıdaki "Yenile" butonuna tıkladığınızda çekilecektir.</p>
            <?php endif; ?>
        </div>

    </div>

    <!-- Tüm Eserler Tablosu -->
    <div style="background: #fff; padding: 20px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.05);">
        <h2 style="margin: 0 0 20px 0; font-size: 1.2rem;">Geçmiş Arşiv</h2>
        <table class="table">
            <thead>
                <tr>
                    <th>Tarih</th>
                    <th>Resim</th>
                    <th>Eser Adı</th>
                    <th>Sanatçı</th>
                    <th>Kaynak</th>
                    <th>İşlemler</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($allArtworks as $art): ?>
                    <tr>
                        <td style="white-space: nowrap;"><?= date('d.m.Y', strtotime($art['date'])) ?></td>
                        <td>
                            <img src="<?= htmlspecialchars($art['thumbnail_url'] ?: $art['image_url']) ?>" style="height: 40px; width: auto; border-radius: 3px;" alt="">
                        </td>
                        <td><?= htmlspecialchars($art['title']) ?></td>
                        <td><?= htmlspecialchars($art['artist']) ?></td>
                        <td>
                            <span style="font-size:0.8rem; background:#f0f0f0; padding:2px 5px; border-radius:3px;">
                                <?= htmlspecialchars($art['description_source']) ?>
                            </span>
                        </td>
                        <td>
                            <button onclick="document.getElementById('edit-modal-<?= $art['id'] ?>').style.display='block'" class="btn" style="padding:3px 8px; font-size:0.8rem;">Düzenle</button>
                            
                            <div id="edit-modal-<?= $art['id'] ?>" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.5); z-index:9999;">
                                <div style="background:#fff; padding:20px; border-radius:8px; width:90%; max-width:600px; max-height:90vh; overflow-y:auto; margin: 5vh auto;">
                                    <h3 style="margin-top:0;">Açıklamayı Düzenle</h3>
                                    <form action="/admin/updateArtDescription" method="POST">
                                        <input type="hidden" name="id" value="<?= $art['id'] ?>">
                                        <div class="form-group" style="margin-top:15px;">
                                            <label>Türkçe Açıklama</label>
                                            <textarea name="description_tr" class="form-control" style="height:200px;" required><?= htmlspecialchars($art['description_tr']) ?></textarea>
                                        </div>
                                        <div style="margin-top:20px; display:flex; justify-content:flex-end; gap:10px;">
                                            <button type="button" class="btn" onclick="document.getElementById('edit-modal-<?= $art['id'] ?>').style.display='none'">İptal</button>
                                            <button type="submit" class="btn btn-primary">Kaydet</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

</div>

<?php require_once ROOT . '/app/Views/admin/layouts/footer.php'; ?>
