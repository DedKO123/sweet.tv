@extends('layouts.app')

@section('content')
    <div class="container mt-5">
        <div class="d-flex justify-content-end">
            <a href="{{ route('lang.switch', ['lang' =>'en']) }}" class="btn btn-primary me-2">{{ __('messages.english') }}</a>
            <a href="{{ route('lang.switch', 'uk') }}" class="btn btn-primary">{{ __('messages.ukrainian') }}</a>
        </div>

        <h1 class="mt-5">{{ __('messages.title') }}</h1>
        <form id="report-form" action="{{ route('report.generate') }}" method="POST">
            @csrf
            <div class="mb-3">
                <label for="maxresult" class="form-label">{{ __('messages.maxresult') }}</label>
                <input type="number" class="form-control" id="maxresult" name="max_results" min="10" max="200" required>
            </div>

            <div class="mb-3">
                <label for="startindex" class="form-label">{{ __('messages.startindex') }}</label>
                <input type="number" class="form-control" id="startindex" name="start_index" min="0" max="2000" required>
            </div>

            <button type="submit" class="btn btn-success">{{ __('messages.generate_report') }}</button>
        </form>

        <div id="message-block" class="alert mt-4" style="display: none;"></div>

        <div id="report-section" class="mt-5" style="display: none;">
            <h2 class="my-4">{{ __('messages.title') }}</h2>

            <div class="row mb-4">
                <div class="col-md-6">
                    <h4>{{ __('messages.sampling_params') }}:</h4>
                    <ul class="list-group">
                        <li class="list-group-item"><strong>{{ __('messages.maxresult') }}:</strong> <span id="report-max-results"></span></li>
                        <li class="list-group-item"><strong>{{ __('messages.startindex') }}:</strong> <span id="report-start-index"></span></li>
                    </ul>
                </div>

                <div class="col-md-6">
                    <h4>{{ __('messages.report_generation_date') }}:</h4>
                    <ul class="list-group">
                        <li class="list-group-item" id="report-created-at"></li>
                    </ul>
                </div>
            </div>

            <h4>{{ __('messages.general_statistic') }}:</h4>
            <table class="table table-bordered">
                <thead>
                <tr>
                    <th>{{ __('messages.params') }}</th>
                    <th>{{ __('messages.values') }}</th>
                </tr>
                </thead>
                <tbody>
                <tr>
                    <td>{{ __('messages.total_movies') }}</td>
                    <td id="total-movies"></td>
                </tr>
                <tr>
                    <td>{{ __('messages.total_countries') }}</td>
                    <td id="total-countries"></td>
                </tr>
                <tr>
                    <td>{{ __('messages.average_actors') }}</td>
                    <td id="average-actors"></td>
                </tr>
                <tr>
                    <td>{{ __('messages.subscription_movies') }}</td>
                    <td id="subscription-movies"></td>
                </tr>
                <tr>
                    <td>{{ __('messages.purchase_movies') }}</td>
                    <td id="purchase-movies"></td>
                </tr>
                </tbody>
            </table>

            <h4>{{ __('messages.total_movies_by_country') }}</h4>
            <div class="mb-4" style="width: 400px; height: 400px;">
                <canvas id="countryChart"></canvas>
            </div>

            <h4>{{ __('messages.movies_by_genre') }}</h4>
            <table class="table table-striped">
                <thead>
                <tr>
                    <th>{{ __('messages.genre') }}</th>
                    <th>{{ __('messages.movies_count') }}</th>
                </tr>
                </thead>
                <tbody id="genre-table-body"></tbody>
            </table>

            <h4>{{ __('messages.keyword_frequency') }}</h4>
            <table class="table table-striped">
                <thead>
                <tr>
                    <th>{{ __('messages.keyword') }}</th>
                    <th>{{ __('messages.frequency') }}</th>
                </tr>
                </thead>
                <tbody id="keyword-table-body"></tbody>
            </table>

            <h4>{{ __('messages.statistic_by_year') }}</h4>
            <div class="mb-4">
                <canvas id="yearChart"></canvas>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <script>
        document.getElementById('report-form').addEventListener('submit', function (event) {
            event.preventDefault();

            let formData = new FormData(this);

            fetch('{{ route('report.generate') }}', {
                method: 'POST',
                body: formData,
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                }
            })
                .then(response => response.json())
                .then(data => {
                    let messageBlock = document.getElementById('message-block');

                    if (data.success) {
                        document.getElementById('report-section').style.display = 'block';

                        document.getElementById('report-max-results').textContent = data.report.max_results;
                        document.getElementById('report-start-index').textContent = data.report.start_index;
                        document.getElementById('report-created-at').textContent = data.report.created_at;

                        document.getElementById('total-movies').textContent = data.report.movies_count;
                        document.getElementById('total-countries').textContent = data.report.total_countries;
                        document.getElementById('average-actors').textContent = data.report.average_actors;
                        document.getElementById('subscription-movies').textContent = data.report.subscription_movies;
                        document.getElementById('purchase-movies').textContent = data.report.purchase_movies;

                        let genreTableBody = document.getElementById('genre-table-body');
                        genreTableBody.innerHTML = '';
                        let genres = Object.entries(data.report.movies_by_genre);
                        genres.sort((a, b) => b[1] - a[1]);

                        for (let [genre, count] of genres) {
                            let row = `<tr><td>${genre}</td><td>${count}</td></tr>`;
                            genreTableBody.innerHTML += row;
                        }


                        let keywordTableBody = document.getElementById('keyword-table-body');
                        keywordTableBody.innerHTML = '';
                        let currentLocale = '{{ app()->getLocale() }}';

                        for (let keyword in data.report.keyword_frequency[currentLocale]) {
                            let count = data.report.keyword_frequency[currentLocale][keyword];
                            let row = `<tr><td>${keyword}</td><td>${count}</td></tr>`;
                            keywordTableBody.innerHTML += row;
                        }
                        updateCharts(data.report);

                        messageBlock.textContent = data.message;
                        messageBlock.className = 'alert alert-success';
                        messageBlock.style.display = 'block';
                    } else {
                        messageBlock.textContent = data.message;
                        messageBlock.className = 'alert alert-danger';
                        messageBlock.style.display = 'block';
                    }
                })
                .catch(error => {
                    let messageBlock = document.getElementById('message-block');
                    messageBlock.textContent = 'Ошибка: ' + error.message;
                    messageBlock.className = 'alert alert-danger';
                    messageBlock.style.display = 'block';
                });
        });

        let countryChart;
        let yearChart;

        function updateCharts(report) {
            let countryData = report.movies_by_country;
            let countryLabels = Object.keys(countryData);
            let countryValues = Object.values(countryData);

            if (countryChart) {
                countryChart.destroy();
            }

            countryChart = new Chart(document.getElementById('countryChart').getContext('2d'), {
                type: 'pie',
                data: {
                    labels: countryLabels,
                    datasets: [{
                        label: 'Количество фильмов по странам',
                        data: countryValues,
                        backgroundColor: ['#FF6384', '#36A2EB', '#FFCE56', '#4BC0C0'],
                        hoverOffset: 4
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                }
            });

            let yearData = report.movies_by_year;
            let yearLabels = Object.keys(yearData);
            let yearValues = Object.values(yearData);

            if (yearChart) {
                yearChart.destroy();
            }

            yearChart = new Chart(document.getElementById('yearChart').getContext('2d'), {
                type: 'line',
                data: {
                    labels: yearLabels,
                    datasets: [{
                        label: 'Количество фильмов по годам',
                        data: yearValues,
                        fill: false,
                        borderColor: 'rgb(75, 192, 192)',
                        tension: 0.1
                    }]
                }
            });
        }
    </script>
@endsection
