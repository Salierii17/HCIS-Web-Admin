<div>
    <div class="clnbd c8tys c58e1 c9gkl cqikb">
        <!-- Your existing header and navigation code remains the same -->
        
        <!-- Page content -->
        <main class="c8c54">
            <section>
                <div class="c9zbf cfacu c0spu cnm0k">
                    <div class="cijys c73bz crfxz ctz8u">
                        <div class="cysna cqlk9">
                            <div class="cmgbb">
                                <div class="ctz8u">
                                    <div class="cnog5">
                                        <a class="cvqf0 crqt4" href="{{route('career.landing_page')}}">
                                            <span class="c8b8n">&lt;-</span> All Jobs
                                        </a>
                                    </div>
                                    <h5 class="c5zpx c9gkl cn95v">Applying for {{$jobDetail?->postingTitle}}</h5>
                                    <div class="c5rk9 coxki">
                                        <form wire:submit.prevent="create" id="career-form">
                                            @csrf
                                            {{ $this->form }}
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </section>
        </main>
    </div>

    @push('scripts')
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // Fix for all checkboxes in the form
        document.querySelectorAll('input[type="checkbox"]').forEach(checkbox => {
            // Remove any existing click handlers
            checkbox.replaceWith(checkbox.cloneNode(true));
            
            // Add new click handler
            checkbox.addEventListener('click', function(e) {
                e.stopPropagation();
                this.checked = !this.checked;
                // Trigger Livewire update
                this.dispatchEvent(new Event('input', { bubbles: true }));
                this.dispatchEvent(new Event('change', { bubbles: true }));
            });
            
            // Make sure it's visible and clickable
            checkbox.style.pointerEvents = 'auto';
            checkbox.style.opacity = '1';
        });
    });

    // Additional fix for Livewire
    document.addEventListener('livewire:init', function() {
        Livewire.hook('element.initialized', (el) => {
            if (el.tagName === 'INPUT' && el.type === 'checkbox') {
                el.style.pointerEvents = 'auto';
                el.style.opacity = '1';
            }
        });
    });
    </script>
    @endpush

    <style>
    /* Force native checkbox appearance */
    input[type="checkbox"] {
        -webkit-appearance: checkbox !important;
        -moz-appearance: checkbox !important;
        appearance: checkbox !important;
        position: relative !important;
        width: 16px !important;
        height: 16px !important;
        margin: 0 !important;
        cursor: pointer !important;
        opacity: 1 !important;
        pointer-events: auto !important;
    }

    /* Remove any overlay that might block clicks */
    .filament-forms-checkbox-component {
        position: relative;
    }
    .filament-forms-checkbox-component::after {
        content: none !important;
    }
    </style>
</div>
