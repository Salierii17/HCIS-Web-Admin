<div>
    <style>
        .active-question {
            border: 3px solid darkblue;
        }
        .no-hover:hover {
            background-color: transparent !important;
            color: inherit !important;
        }
        .disabled-radio {
            cursor: not-allowed;
        }
        .disable-button {
            pointer-events: none;
            opacity: 0.6;
        }

    </style>
    <div class="container mt-4">
        <div class="row">
            <h4>{{$package->name}}</h4>
            <div class="col-md-8">
                <div id="question-container">
                    
                    <div class="card question-card">
                        @if($tryOut->finished_at == null)
                            <div class="countdown-timer mb-4 text-success" id="countdown">
                                Waktu tersisa: <span id="time">00:00:00</span>
                            </div>
                        @endif
                        <div class="card-body">
                            <p class="card-text">{{ strip_tags($currentPackageQuestion->question->question) }}</p>
                            @foreach($currentPackageQuestion->question->options as $item)
                                <div class="form-check">
                                    <input class="form-check-input" 
                                        wire:model="selectedAnswers.{{$currentPackageQuestion->question_id}}"
                                        wire:click="saveAnswer({{$currentPackageQuestion->question_id}}, {{$item->id}})"
                                        type="radio"
                                        @if($timeLeft <= 0)
                                            disabled
                                            class="disable-radio"
                                        @endif
                                        name="question" 
                                        value="{{$item->id}}">
                                       
                                    <label class="form-check-label">{{ strip_tags($item->option_text) }}</label>
                                </div>
                            @endforeach
                           
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card question-navigation">
                    <div class="card-body">
                        <h5 class="card-title">Navigation</h5>
                        <div class="row" role="group">
                            @foreach($questions as $index => $item)
                                @php
                                    $isAnswered = isset($selectedAnswers[$item->question_id]) && !is_null($selectedAnswers[$item->question_id]); 
                                    $isActive = $currentPackageQuestion->question->id === $item->question_id;
                                @endphp
                               <div class="col-2 mb-2">
                                    <button 
                                        @if($timeLeft <= 0)
                                            disabled
                                        @endif
                                        wire:click="goToQuestion({{$item->id}})" 
                                        class="btn {{$isAnswered ? 'btn-primary' : 'btn-outline-primary no-hover'}} {{$isActive ? 'active-question' : ''}}">{{$index+1}}</button>
                            </div>
                                
                            @endforeach
                        </div>
                        <button wire:click="submit" onclick="return confirm('Apakah anda yakin ingin mengirim jawaban ini?')" class="btn btn-primary mt-3 w-100">Submit</button>
                    </div>

                </div>

            </div>

        </div>
        @if(session()->has('message'))
            <div class="alert alert-success text-center">
                {{ session('message') }} <a href="{{url('tryouts')}}">Lihat Hasil Pengerjaan</a>
            </div>
        @endif

    </div>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            let timeLeft = {{$timeLeft}};
            startCountdown(timeLeft, document.getElementById('time'));
        });

        function startCountdown(duration, display) {
            let timer = duration, minutes, seconds;
            setInterval(function() {
                hours = parseInt(timer / 3600, 10);
                minutes = parseInt((timer % 3600) / 60, 10);
                seconds = parseInt(timer % 60, 10);

                hours = hours < 10 ? "0" + hours : hours;
                minutes = minutes < 10 ? "0" + minutes : minutes;
                seconds = seconds < 10 ? "0" + seconds : seconds;

                display.textContent = hours + ":" + minutes + ":" + seconds;

                if (--timer < 0) {
                    timer = 0;
                }
            }, 1000);
        }
    </script>
</div>
