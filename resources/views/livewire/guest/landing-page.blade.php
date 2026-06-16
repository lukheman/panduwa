<div>
    <!-- Hero Section -->
    <section class="hero position-relative vh-100 d-flex align-items-center" style="background: url('{{ asset('assets/images/kantor-desa.png') }}') center/cover no-repeat; margin-top: -73px; padding-top: 73px;">
        <!-- Dark Overlay for text readability -->
        <div class="position-absolute top-0 start-0 w-100 h-100 bg-dark" style="opacity: 0.6; z-index: 0;"></div>
        
        <div class="container position-relative z-1 text-white text-center">
            <div class="row justify-content-center">
                <div class="col-lg-8">
                    <span class="badge bg-white text-dark px-3 py-2 rounded-pill mb-4 fw-semibold shadow-sm">
                        <i class="fas fa-map-marker-alt text-danger me-1"></i> Selamat Datang di Portal Resmi
                    </span>
                    <h1 class="display-3 fw-bold mb-4 text-white text-shadow-sm">
                        Desa Waindawula
                    </h1>
                    <p class="lead mb-5 text-light text-shadow-sm">
                        Menuju desa yang mandiri, sejahtera, dan berbudaya dengan pemanfaatan teknologi informasi untuk pelayanan publik yang lebih transparan dan efisien.
                    </p>
                    <div class="d-flex flex-column flex-sm-row gap-3 justify-content-center">
                        <a href="#lokasi" class="btn btn-primary btn-lg px-4 py-3 rounded-pill shadow">
                            <i class="fas fa-map-marker-alt me-2"></i> Lokasi Desa
                        </a>
                        <a href="{{ route('login') }}" class="btn btn-outline-light btn-lg px-4 py-3 rounded-pill">
                            <i class="fas fa-desktop me-2"></i> Login Aparatur Desa
                        </a>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Shape Divider -->
        <div class="position-absolute bottom-0 start-0 w-100 overflow-hidden" style="line-height: 0;">
            <svg viewBox="0 0 1200 120" preserveAspectRatio="none" style="display: block; width: 100%; height: 60px; fill: #ffffff;">
                <path d="M321.39,56.44c58-10.79,114.16-30.13,172-41.86,82.39-16.72,168.19-17.73,250.45-.39C823.78,31,906.67,72,985.66,92.83c70.05,18.48,146.53,26.09,214.34,3V120H0V95.8C59.71,118.08,130.83,119.3,198.71,108.68,241.1,101.42,283.47,78.53,321.39,56.44Z"></path>
            </svg>
        </div>
    </section>
    <!-- Lokasi Desa Section -->
    <section id="lokasi" class="py-5 bg-white">
        <div class="container py-5">
            <div class="row g-5 align-items-center">
                <div class="col-lg-6">
                    <span class="badge bg-primary bg-opacity-10 text-primary px-3 py-2 rounded-pill mb-3 fw-semibold">Lokasi Kami</span>
                    <h2 class="fw-bold mb-4">Peta Lokasi Desa Waindawula</h2>
                    <p class="text-secondary mb-4" style="line-height: 1.8;">
                        Desa Waindawula terletak di lokasi yang strategis dengan akses yang mudah dijangkau. Kunjungi Kantor Kepala Desa kami untuk mendapatkan informasi dan layanan administrasi publik secara langsung pada jam kerja operasional (Senin - Jumat, 08:00 - 15:00 WIB).
                    </p>
                    <div class="d-flex align-items-center mb-3">
                        <div class="bg-danger bg-opacity-10 text-danger rounded-circle p-3 me-3">
                            <i class="fas fa-map-marker-alt fs-5"></i>
                        </div>
                        <div>
                            <h5 class="fw-bold mb-1">Alamat Lengkap</h5>
                            <p class="text-muted small mb-0">Desa Waindawula</p>
                        </div>
                    </div>
                    <div class="d-flex align-items-center">
                        <div class="bg-success bg-opacity-10 text-success rounded-circle p-3 me-3">
                            <i class="fas fa-phone-alt fs-5"></i>
                        </div>
                        <div>
                            <h5 class="fw-bold mb-1">Kontak Informasi</h5>
                            <p class="text-muted small mb-0">(0298) 1234567</p>
                        </div>
                    </div>
                </div>
                <div class="col-lg-6">
                    <div class="rounded-4 overflow-hidden shadow-lg border border-light" style="height: 400px; background: #e9ecef;">
                        <!-- Embed Google Maps -->
                        <iframe 
                            src="https://maps.google.com/maps?q=-5.673305,122.5191325&t=&z=15&ie=UTF8&iwloc=&output=embed" 
                            width="100%" 
                            height="100%" 
                            style="border:0;" 
                            allowfullscreen="" 
                            loading="lazy" 
                            referrerpolicy="no-referrer-when-downgrade">
                        </iframe>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <x-slot:styles>
        <style>
            .text-shadow-sm {
                text-shadow: 0 2px 4px rgba(0,0,0,0.5);
            }
            .transition-hover {
                transition: transform 0.3s ease, box-shadow 0.3s ease;
            }
            .transition-hover:hover {
                transform: translateY(-5px);
                box-shadow: 0 1rem 3rem rgba(0,0,0,.175)!important;
            }
        </style>
    </x-slot:styles>
</div>
