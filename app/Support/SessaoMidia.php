<?php

declare(strict_types=1);

namespace App\Support;

final class SessaoMidia
{
    public static function html(?int $midia, array $imagens, string $slug = 'sessao'): string
    {
        if ($midia === null || empty($imagens)) {
            return '';
        }

        return match ($midia) {
            0 => self::renderGaleria($imagens, $slug),
            1 => self::renderCarrossel($imagens, $slug),
            default => '',
        };
    }

    private static function renderGaleria(array $imagens, string $slug): string
    {
        $html = '';

        $html .= '<section class="py-5">';
        $html .= '<div class="container">';
        $html .= '<div class="section-header text-center mb-4" data-aos="fade-up">';
        $html .= '<h2 class="section-title">Galeria</h2>';
        $html .= '</div>';
        $html .= '<div class="row g-3" id="galeria-' . $slug . '">';

        foreach ($imagens as $idx => $img) {
            $path = htmlspecialchars((string) ($img['path'] ?? ''), ENT_QUOTES, 'UTF-8');
            $legenda = htmlspecialchars((string) ($img['legenda'] ?? ''), ENT_QUOTES, 'UTF-8');

            $html .= '<div class="col-md-4 col-lg-3" data-aos="fade-up" data-aos-delay="' . (50 * ($idx % 8)) . '">';
            $html .= '<a href="/' . $path . '" class="glightbox-item d-block rounded-3 overflow-hidden border" data-gallery="g-' . $slug . '" data-caption="' . $legenda . '" style="border-color:var(--border-color,#dee2e6)!important;box-shadow:0 2px 8px rgba(0,0,0,0.08);">';
            $html .= '<img src="/' . $path . '" alt="' . $legenda . '" class="img-fluid w-100" style="height:220px;object-fit:cover;cursor:pointer;">';
            $html .= '</a>';
            $html .= '</div>';
        }

        $html .= '</div>';
        $html .= '</div>';
        $html .= '</section>';

        $modalId = 'lightboxModal-' . $slug;

        $html .= '<style>
        #' . $modalId . ' ~ .modal-backdrop.show { opacity: 0.9; }
        </style>';
        $html .= '<div class="modal fade" id="' . $modalId . '" tabindex="-1" aria-hidden="true">';
        $html .= '<div class="modal-dialog modal-xl modal-dialog-centered">';
        $html .= '<div class="modal-content bg-transparent border-0">';
        $html .= '<button type="button" class="btn-close btn-close-white position-absolute top-0 end-0 z-3 m-3" data-bs-dismiss="modal" aria-label="Fechar"></button>';
        $html .= '<div class="modal-body text-center p-0">';
        $html .= '<img id="lightboxImg-' . $slug . '" src="" alt="" class="img-fluid rounded-3" style="max-height:85vh;">';
        $html .= '<p id="lightboxCaption-' . $slug . '" class="text-white mt-2 mb-0 small"></p>';
        $html .= '</div>';
        $html .= '<button class="btn btn-dark position-absolute top-50 start-0 translate-middle-y ms-2 rounded-circle" id="lightboxPrev-' . $slug . '" style="width:44px;height:44px;z-index:10;">&lsaquo;</button>';
        $html .= '<button class="btn btn-dark position-absolute top-50 end-0 translate-middle-y me-2 rounded-circle" id="lightboxNext-' . $slug . '" style="width:44px;height:44px;z-index:10;">&rsaquo;</button>';
        $html .= '</div>';
        $html .= '</div>';
        $html .= '</div>';

        $html .= '<script>';
        $html .= 'document.addEventListener("DOMContentLoaded",function(){';
        $html .= 'var items=document.querySelectorAll("#galeria-' . $slug . ' a");';
        $html .= 'var modal=new bootstrap.Modal(document.getElementById("' . $modalId . '"));';
        $html .= 'var img=document.getElementById("lightboxImg-' . $slug . '");';
        $html .= 'var caption=document.getElementById("lightboxCaption-' . $slug . '");';
        $html .= 'var prevBtn=document.getElementById("lightboxPrev-' . $slug . '");';
        $html .= 'var nextBtn=document.getElementById("lightboxNext-' . $slug . '");';
        $html .= 'var currentIdx=0,data=[];';
        $html .= 'items.forEach(function(a,i){';
        $html .= 'data.push({src:a.href,caption:a.dataset.caption||""});';
        $html .= 'a.addEventListener("click",function(e){e.preventDefault();currentIdx=i;showImg();modal.show();});';
        $html .= '});';
        $html .= 'function showImg(){';
        $html .= 'img.src=data[currentIdx].src;';
        $html .= 'caption.textContent=data[currentIdx].caption;';
        $html .= 'prevBtn.style.display=data.length>1?"":"none";';
        $html .= 'nextBtn.style.display=data.length>1?"":"none";';
        $html .= '}';
        $html .= 'prevBtn.addEventListener("click",function(){currentIdx=(currentIdx-1+data.length)%data.length;showImg();});';
        $html .= 'nextBtn.addEventListener("click",function(){currentIdx=(currentIdx+1)%data.length;showImg();});';
        $html .= 'document.addEventListener("keydown",function(e){';
        $html .= 'if(!document.getElementById("' . $modalId . '").classList.contains("show"))return;';
        $html .= 'if(e.key==="ArrowLeft"){currentIdx=(currentIdx-1+data.length)%data.length;showImg();}';
        $html .= 'if(e.key==="ArrowRight"){currentIdx=(currentIdx+1)%data.length;showImg();}';
        $html .= 'if(e.key==="Escape")modal.hide();';
        $html .= '});';
        $html .= '});';
        $html .= '</script>';

        return $html;
    }

    private static function renderCarrossel(array $imagens, string $slug): string
    {
        $carouselId = 'carrossel-' . $slug;
        $html = '';

        $html .= '<section class="py-5">';
        $html .= '<div class="container">';
        $html .= '<div id="' . $carouselId . '" class="carousel slide carousel-fade" data-bs-ride="carousel" data-bs-interval="5000">';

        $html .= '<div class="carousel-indicators">';
        foreach ($imagens as $idx => $img) {
            $html .= '<button type="button" data-bs-target="#' . $carouselId . '" data-bs-slide-to="' . $idx . '" class="' . ($idx === 0 ? 'active' : '') . '" aria-current="' . ($idx === 0 ? 'true' : 'false') . '" aria-label="Slide ' . ($idx + 1) . '"></button>';
        }
        $html .= '</div>';

        $html .= '<div class="carousel-inner rounded-3 overflow-hidden">';
        foreach ($imagens as $idx => $img) {
            $path = htmlspecialchars((string) ($img['path'] ?? ''), ENT_QUOTES, 'UTF-8');
            $legenda = htmlspecialchars((string) ($img['legenda'] ?? ''), ENT_QUOTES, 'UTF-8');

            $html .= '<div class="carousel-item ' . ($idx === 0 ? 'active' : '') . '">';
            $html .= '<img src="/' . $path . '" alt="' . $legenda . '" class="d-block w-100" style="height:400px;object-fit:cover;">';
            if ($legenda !== '') {
                $html .= '<div class="carousel-caption d-none d-md-block"><p class="mb-0">' . $legenda . '</p></div>';
            }
            $html .= '</div>';
        }
        $html .= '</div>';

        $html .= '<button class="carousel-control-prev" type="button" data-bs-target="#' . $carouselId . '" data-bs-slide="prev">';
        $html .= '<span class="carousel-control-prev-icon" aria-hidden="true"></span>';
        $html .= '<span class="visually-hidden">Anterior</span>';
        $html .= '</button>';

        $html .= '<button class="carousel-control-next" type="button" data-bs-target="#' . $carouselId . '" data-bs-slide="next">';
        $html .= '<span class="carousel-control-next-icon" aria-hidden="true"></span>';
        $html .= '<span class="visually-hidden">Próximo</span>';
        $html .= '</button>';

        $html .= '</div>';
        $html .= '</div>';
        $html .= '</section>';

        return $html;
    }
}
