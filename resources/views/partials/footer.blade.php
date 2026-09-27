<footer class="sipat-footer" id="kontak">
    <div class="container py-5">
        <div class="row g-4">
            <div class="col-lg-5">
                <div class="d-flex align-items-center gap-2 mb-3">
                    <span class="brand-mark brand-mark-light"><x-si-icon name="pulse" /></span>
                    <div class="lh-1">
                        <div class="fw-bold">SIPAT</div>
                        <small class="text-white-50">{{ config('klinik.nama') }}</small>
                    </div>
                </div>
                <p class="text-white-50 small mb-3">
                    Sistem Antrian Online Terpadu: pendaftaran online untuk pasien umum dan satu nomor urut
                    yang adil untuk pasien umum maupun BPJS.
                </p>
                <div class="d-flex flex-wrap gap-2">
                    <span class="chip chip-dark"><x-si-icon name="ticket" class="sipat-icon-sm" /> Satu nomor urut</span>
                    <span class="chip chip-dark"><x-si-icon name="clock" class="sipat-icon-sm" /> Estimasi jam datang</span>
                    <span class="chip chip-dark"><x-si-icon name="shield" class="sipat-icon-sm" /> Data pasien dilindungi</span>
                </div>
            </div>

            <div class="col-6 col-lg-3">
                <h2 class="footer-title">Jam Layanan</h2>
                <ul class="list-unstyled small mb-0 footer-list">
                    @foreach (config('klinik.jam') as $baris)
                        <li>
                            <span class="text-white-50">{{ $baris['hari'] }}</span>
                            <span class="d-block fw-semibold">{{ $baris['jam'] }}</span>
                        </li>
                    @endforeach
                </ul>
            </div>

            <div class="col-6 col-lg-4">
                <h2 class="footer-title">Kontak</h2>
                <ul class="list-unstyled small mb-2 footer-list">
                    <li>
                        <x-si-icon name="map-pin" class="sipat-icon-sm" />
                        <span>{{ config('klinik.alamat') }}</span>
                    </li>
                    <li>
                        <x-si-icon name="phone" class="sipat-icon-sm" />
                        <span>Telepon {{ config('klinik.telepon') }}</span>
                    </li>
                    <li>
                        <x-si-icon name="info" class="sipat-icon-sm" />
                        <span>WhatsApp {{ config('klinik.whatsapp') }}</span>
                    </li>
                    <li>
                        <x-si-icon name="mail" class="sipat-icon-sm" />
                        <span>{{ config('klinik.email') }}</span>
                    </li>
                </ul>
                @if (config('klinik.contoh'))
                    <p class="small text-warning-emphasis mb-0">
                        Data kontak di atas masih contoh dan belum diverifikasi klinik.
                    </p>
                @endif
            </div>
        </div>

        <hr class="footer-rule">

        <div class="d-flex flex-wrap justify-content-between gap-2 small text-white-50">
            <span>&copy; {{ now()->year }} {{ config('klinik.nama') }} — SIPAT</span>
            <span>Sistem Antrian Online Terpadu</span>
        </div>
    </div>
</footer>
