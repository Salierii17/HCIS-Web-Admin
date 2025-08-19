<?php

namespace App\Livewire;

// use AbanoubNassem\FilamentGRecaptchaField\Forms\Components\GRecaptcha;
// use Afatmustafa\FilamentTurnstile\Forms\Components\Turnstile;
use App\Filament\Enums\AttachmentCategory;
use App\Filament\Enums\JobCandidateStatus;
use App\Models\Attachments;
use App\Models\Candidates;
use App\Models\JobCandidates;
use App\Models\JobOpenings;
// use DominionSolutions\FilamentCaptcha\Forms\Components\Captcha;
use Closure;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Actions\Contracts\HasActions;
use Filament\Forms;
use Filament\Forms\Components\Actions\Action;
use Filament\Forms\Components\Wizard;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;

class CareerApplyJob extends Component implements HasActions, HasForms
{
    use InteractsWithActions;
    use InteractsWithForms;

    public ?array $data = [
        'attachment' => null,
        'country_code' => '+62', // Add this default value
        'email_valid' => false,
    ];

    // public ?string $captcha = '';

    public string|null|JobOpenings $record = '';

    public static ?JobOpenings $jobDetails = null;

    public ?string $referenceNumber;

    public function mount($jobReferenceNumber)
    {
        // search for the job reference number, if not valid, redirect to all job
        $this->jobOpeningDetails($jobReferenceNumber);
        $this->referenceNumber = $jobReferenceNumber;

    }

    public function updated()
    {
        $this->jobOpeningDetails($this->referenceNumber);
    }

    private function jobOpeningDetails($reference): void
    {
        $this->record = JobOpenings::jobStillOpen()->where('JobOpeningSystemID', '=', $reference)->first();
        if (empty($this->record)) {
            // redirect back as the job opening is closed or tampered id or not existing
            Notification::make()
                ->title('Job Opening is already closed or doesn\'t exist.')
                ->icon('heroicon-o-x-circle')
                ->iconColor('warning')
                ->send();
            $this->redirectRoute('career.landing_page');
        }
    }

    public function create(): void
    {
        $data = $this->form->getState();

        // Ensure country_code exists in the data
        $data['country_code'] = $data['country_code'] ?? '+62';

        // Combine country code with mobile number
        $fullMobileNumber = $data['country_code'].$data['mobile'];

        // Create Candidate
        $candidate = Candidates::create([
            'FirstName' => $data['FirstName'],
            'LastName' => $data['LastName'],
            'Mobile' => $fullMobileNumber, // Store with country code
            'email' => $data['Email'],
            'ExperienceInYears' => $data['experience'],
            'Street' => $data['Street'],
            'City' => $data['City'],
            'Country' => $data['Country'],
            'ZipCode' => $data['ZipCode'],
            'State' => $data['State'],
            'CurrentEmployer' => $data['CurrentEmployer'],
            'CurrentJobTitle' => $data['CurrentJobTitle'],
            'School' => $data['School'],
            'ExperienceDetails' => $data['ExperienceDetails'],
        ]);

        // Job Candidates
        $job_candidates = JobCandidates::create([
            'JobId' => $this->record->id,
            'CandidateSource' => 'Career Page',
            'CandidateStatus' => JobCandidateStatus::New,
            'candidate' => $candidate->id,
            'mobile' => $fullMobileNumber, // Store with country code
            'Email' => $data['Email'],
            'country_code' => $data['country_code'] ?? '+62', // Store country code separately if needed
            'ExperienceInYears' => $data['experience'],
            'CurrentJobTitle' => $data['CurrentJobTitle'],
            'CurrentEmployer' => $data['CurrentEmployer'],
            'Street' => $data['Street'],
            'City' => $data['City'],
            'Country' => $data['Country'],
            'ZipCode' => $data['ZipCode'],
            'State' => $data['State'],
        ]);

        // Store the resume file in attachments table
        if (isset($data['attachment'])) {
            $this->storeResumeAttachment($data['attachment'], $candidate, $job_candidates);
        }

        $this->sendSuccessNotifications();
    }

    protected function storeResumeAttachment(string $filePath, Candidates $candidate, JobCandidates $jobCandidate): void
    {
        // Get just the filename from the path
        $fileName = basename($filePath);

        // Store attachment linked to Candidate
        $candidate->attachments()->create([
            'attachment' => $filePath,
            'attachmentName' => $fileName, // This will now be the unique filename
            'category' => AttachmentCategory::Resume->value,
            'moduleName' => 'Candidates',
        ]);

        // Store attachment linked to JobCandidate
        $jobCandidate->attachments()->create([
            'attachment' => $filePath,
            'attachmentName' => $fileName, // This will now be the unique filename
            'category' => AttachmentCategory::Resume->value,
            'moduleName' => 'JobCandidates',
        ]);
    }

    protected function sendSuccessNotifications(): void
    {
        Notification::make()
            ->title('Application submitted!')
            ->success()
            ->body('Thank you for submitting your application details.')
            ->send();

        Notification::make()
            ->title('Reminder!')
            ->success()
            ->body('Please always check your communication for our hiring party response.')
            ->send();

        $this->redirectRoute('career.landing_page');
    }

    public function form(Form $form): Form
    {
        return $form
            ->statePath('data')
            ->schema([
                Wizard::make([
                    Wizard\Step::make('Application')
                        ->icon('heroicon-o-user')
                        ->columns(2)
                        ->schema(array_merge(
                            $this->applicationStepWizard(),
                            [
                                Forms\Components\Grid::make(1)
                                    ->columns(1),
                            ]
                            // ->schema($this->captchaField())
                        )),
                    Wizard\Step::make('Assessment')
                        ->visible(false)
                        ->icon('heroicon-o-user')
                        ->columns(2)
                        ->schema(array_merge([], $this->assessmentStepWizard())),
                ])
                    ->nextAction(
                        fn (Action $action) => $action->view('career-form.apply-job-components.NextActionButton'),
                    )
                    ->submitAction(view('career-form.apply-job-components.SubmitApplicationButton')),
            ]);
    }

    private function assessmentStepWizard(): Wizard\Step|array
    {
        return [];
    }

    private function applicationStepWizard(): array
    {
        return [
            Forms\Components\Section::make('Basic Information')
                ->columns(2)
                ->schema([
                    Forms\Components\TextInput::make('FirstName')
                        ->required()
                        ->label('First Name')
                        ->placeholder('John'),
                    Forms\Components\TextInput::make('LastName')
                        ->required()
                        ->label('Last Name')
                        ->placeholder('Doe'),
                    Forms\Components\Select::make('country_code')
                        ->label('Country Code')
                        ->default('+62')
                        ->options([
                            '+1' => 'United States/Canada (+1)',
                            '+7' => 'Russia/Kazakhstan (+7)',
                            '+20' => 'Egypt (+20)',
                            '+27' => 'South Africa (+27)',
                            '+30' => 'Greece (+30)',
                            '+31' => 'Netherlands (+31)',
                            '+32' => 'Belgium (+32)',
                            '+33' => 'France (+33)',
                            '+34' => 'Spain (+34)',
                            '+36' => 'Hungary (+36)',
                            '+39' => 'Italy (+39)',
                            '+40' => 'Romania (+40)',
                            '+41' => 'Switzerland (+41)',
                            '+43' => 'Austria (+43)',
                            '+44' => 'United Kingdom (+44)',
                            '+45' => 'Denmark (+45)',
                            '+46' => 'Sweden (+46)',
                            '+47' => 'Norway (+47)',
                            '+48' => 'Poland (+48)',
                            '+49' => 'Germany (+49)',
                            '+51' => 'Peru (+51)',
                            '+52' => 'Mexico (+52)',
                            '+53' => 'Cuba (+53)',
                            '+54' => 'Argentina (+54)',
                            '+55' => 'Brazil (+55)',
                            '+56' => 'Chile (+56)',
                            '+57' => 'Colombia (+57)',
                            '+58' => 'Venezuela (+58)',
                            '+60' => 'Malaysia (+60)',
                            '+61' => 'Australia (+61)',
                            '+62' => 'Indonesia (+62)',
                            '+63' => 'Philippines (+63)',
                            '+64' => 'New Zealand (+64)',
                            '+65' => 'Singapore (+65)',
                            '+66' => 'Thailand (+66)',
                            '+81' => 'Japan (+81)',
                            '+82' => 'South Korea (+82)',
                            '+84' => 'Vietnam (+84)',
                            '+86' => 'China (+86)',
                            '+90' => 'Turkey (+90)',
                            '+91' => 'India (+91)',
                            '+92' => 'Pakistan (+92)',
                            '+93' => 'Afghanistan (+93)',
                            '+94' => 'Sri Lanka (+94)',
                            '+95' => 'Myanmar (+95)',
                            '+98' => 'Iran (+98)',
                            '+212' => 'Morocco (+212)',
                            '+213' => 'Algeria (+213)',
                            '+216' => 'Tunisia (+216)',
                            '+218' => 'Libya (+218)',
                            '+220' => 'Gambia (+220)',
                            '+221' => 'Senegal (+221)',
                            '+222' => 'Mauritania (+222)',
                            '+223' => 'Mali (+223)',
                            '+224' => 'Guinea (+224)',
                            '+225' => 'Ivory Coast (+225)',
                            '+226' => 'Burkina Faso (+226)',
                            '+227' => 'Niger (+227)',
                            '+228' => 'Togo (+228)',
                            '+229' => 'Benin (+229)',
                            '+230' => 'Mauritius (+230)',
                            '+231' => 'Liberia (+231)',
                            '+232' => 'Sierra Leone (+232)',
                            '+233' => 'Ghana (+233)',
                            '+234' => 'Nigeria (+234)',
                            '+235' => 'Chad (+235)',
                            '+236' => 'Central African Republic (+236)',
                            '+237' => 'Cameroon (+237)',
                            '+238' => 'Cape Verde (+238)',
                            '+239' => 'Sao Tome and Principe (+239)',
                            '+240' => 'Equatorial Guinea (+240)',
                            '+241' => 'Gabon (+241)',
                            '+242' => 'Republic of the Congo (+242)',
                            '+243' => 'DR Congo (+243)',
                            '+244' => 'Angola (+244)',
                            '+245' => 'Guinea-Bissau (+245)',
                            '+248' => 'Seychelles (+248)',
                            '+249' => 'Sudan (+249)',
                            '+250' => 'Rwanda (+250)',
                            '+251' => 'Ethiopia (+251)',
                            '+252' => 'Somalia (+252)',
                            '+253' => 'Djibouti (+253)',
                            '+254' => 'Kenya (+254)',
                            '+255' => 'Tanzania (+255)',
                            '+256' => 'Uganda (+256)',
                            '+257' => 'Burundi (+257)',
                            '+258' => 'Mozambique (+258)',
                            '+260' => 'Zambia (+260)',
                            '+261' => 'Madagascar (+261)',
                            '+262' => 'Reunion (+262)',
                            '+263' => 'Zimbabwe (+263)',
                            '+264' => 'Namibia (+264)',
                            '+265' => 'Malawi (+265)',
                            '+266' => 'Lesotho (+266)',
                            '+267' => 'Botswana (+267)',
                            '+268' => 'Eswatini (+268)',
                            '+269' => 'Comoros (+269)',
                            '+290' => 'Saint Helena (+290)',
                            '+291' => 'Eritrea (+291)',
                            '+297' => 'Aruba (+297)',
                            '+298' => 'Faroe Islands (+298)',
                            '+299' => 'Greenland (+299)',
                            '+350' => 'Gibraltar (+350)',
                            '+351' => 'Portugal (+351)',
                            '+352' => 'Luxembourg (+352)',
                            '+353' => 'Ireland (+353)',
                            '+354' => 'Iceland (+354)',
                            '+355' => 'Albania (+355)',
                            '+356' => 'Malta (+356)',
                            '+357' => 'Cyprus (+357)',
                            '+358' => 'Finland (+358)',
                            '+359' => 'Bulgaria (+359)',
                            '+370' => 'Lithuania (+370)',
                            '+371' => 'Latvia (+371)',
                            '+372' => 'Estonia (+372)',
                            '+373' => 'Moldova (+373)',
                            '+374' => 'Armenia (+374)',
                            '+375' => 'Belarus (+375)',
                            '+376' => 'Andorra (+376)',
                            '+377' => 'Monaco (+377)',
                            '+378' => 'San Marino (+378)',
                            '+379' => 'Vatican City (+379)',
                            '+380' => 'Ukraine (+380)',
                            '+381' => 'Serbia (+381)',
                            '+382' => 'Montenegro (+382)',
                            '+383' => 'Kosovo (+383)',
                            '+385' => 'Croatia (+385)',
                            '+386' => 'Slovenia (+386)',
                            '+387' => 'Bosnia and Herzegovina (+387)',
                            '+389' => 'North Macedonia (+389)',
                            '+420' => 'Czech Republic (+420)',
                            '+421' => 'Slovakia (+421)',
                            '+423' => 'Liechtenstein (+423)',
                            '+500' => 'Falkland Islands (+500)',
                            '+501' => 'Belize (+501)',
                            '+502' => 'Guatemala (+502)',
                            '+503' => 'El Salvador (+503)',
                            '+504' => 'Honduras (+504)',
                            '+505' => 'Nicaragua (+505)',
                            '+506' => 'Costa Rica (+506)',
                            '+507' => 'Panama (+507)',
                            '+508' => 'Saint Pierre and Miquelon (+508)',
                            '+509' => 'Haiti (+509)',
                            '+590' => 'Guadeloupe (+590)',
                            '+591' => 'Bolivia (+591)',
                            '+592' => 'Guyana (+592)',
                            '+593' => 'Ecuador (+593)',
                            '+594' => 'French Guiana (+594)',
                            '+595' => 'Paraguay (+595)',
                            '+596' => 'Martinique (+596)',
                            '+597' => 'Suriname (+597)',
                            '+598' => 'Uruguay (+598)',
                            '+599' => 'Curaçao (+599)',
                            '+670' => 'Timor-Leste (+670)',
                            '+672' => 'Norfolk Island (+672)',
                            '+673' => 'Brunei (+673)',
                            '+674' => 'Nauru (+674)',
                            '+675' => 'Papua New Guinea (+675)',
                            '+676' => 'Tonga (+676)',
                            '+677' => 'Solomon Islands (+677)',
                            '+678' => 'Vanuatu (+678)',
                            '+679' => 'Fiji (+679)',
                            '+680' => 'Palau (+680)',
                            '+681' => 'Wallis and Futuna (+681)',
                            '+682' => 'Cook Islands (+682)',
                            '+683' => 'Niue (+683)',
                            '+685' => 'Samoa (+685)',
                            '+686' => 'Kiribati (+686)',
                            '+687' => 'New Caledonia (+687)',
                            '+688' => 'Tuvalu (+688)',
                            '+689' => 'French Polynesia (+689)',
                            '+690' => 'Tokelau (+690)',
                            '+691' => 'Micronesia (+691)',
                            '+692' => 'Marshall Islands (+692)',
                            '+850' => 'North Korea (+850)',
                            '+852' => 'Hong Kong (+852)',
                            '+853' => 'Macau (+853)',
                            '+855' => 'Cambodia (+855)',
                            '+856' => 'Laos (+856)',
                            '+880' => 'Bangladesh (+880)',
                            '+886' => 'Taiwan (+886)',
                            '+960' => 'Maldives (+960)',
                            '+961' => 'Lebanon (+961)',
                            '+962' => 'Jordan (+962)',
                            '+963' => 'Syria (+963)',
                            '+964' => 'Iraq (+964)',
                            '+965' => 'Kuwait (+965)',
                            '+966' => 'Saudi Arabia (+966)',
                            '+967' => 'Yemen (+967)',
                            '+968' => 'Oman (+968)',
                            '+970' => 'Palestine (+970)',
                            '+971' => 'United Arab Emirates (+971)',
                            '+972' => 'Israel (+972)',
                            '+973' => 'Bahrain (+973)',
                            '+974' => 'Qatar (+974)',
                            '+975' => 'Bhutan (+975)',
                            '+976' => 'Mongolia (+976)',
                            '+977' => 'Nepal (+977)',
                            '+992' => 'Tajikistan (+992)',
                            '+993' => 'Turkmenistan (+993)',
                            '+994' => 'Azerbaijan (+994)',
                            '+995' => 'Georgia (+995)',
                            '+996' => 'Kyrgyzstan (+996)',
                            '+998' => 'Uzbekistan (+998)',
                        ])
                        ->searchable()
                        ->required()
                        ->live()
                        ->columnSpan(1),

                    Forms\Components\TextInput::make('mobile')
                        ->required()
                        ->tel()
                        ->label('Mobile Number')
                        ->placeholder(function ($get) {
                            $countryCode = $get('country_code') ?? '+62';
                            switch ($countryCode) {
                                case '+1':
                                    return 'e.g. 2015550123'; // USA/Canada
                                case '+7':
                                    return 'e.g. 9123456789'; // Russia
                                case '+20':
                                    return 'e.g. 1001234567'; // Egypt
                                case '+27':
                                    return 'e.g. 821234567'; // South Africa
                                case '+30':
                                    return 'e.g. 6912345678'; // Greece
                                case '+31':
                                    return 'e.g. 612345678'; // Netherlands
                                case '+32':
                                    return 'e.g. 470123456'; // Belgium
                                case '+33':
                                    return 'e.g. 612345678'; // France
                                case '+34':
                                    return 'e.g. 612345678'; // Spain
                                case '+36':
                                    return 'e.g. 201234567'; // Hungary
                                case '+39':
                                    return 'e.g. 3123456789'; // Italy
                                case '+40':
                                    return 'e.g. 712345678'; // Romania
                                case '+41':
                                    return 'e.g. 781234567'; // Switzerland
                                case '+43':
                                    return 'e.g. 664123456'; // Austria
                                case '+44':
                                    return 'e.g. 7123456789'; // UK
                                case '+45':
                                    return 'e.g. 20123456'; // Denmark
                                case '+46':
                                    return 'e.g. 701234567'; // Sweden
                                case '+47':
                                    return 'e.g. 41234567'; // Norway
                                case '+48':
                                    return 'e.g. 501234567'; // Poland
                                case '+49':
                                    return 'e.g. 15123456789'; // Germany
                                case '+51':
                                    return 'e.g. 912345678'; // Peru
                                case '+52':
                                    return 'e.g. 12221234567'; // Mexico
                                case '+53':
                                    return 'e.g. 51234567'; // Cuba
                                case '+54':
                                    return 'e.g. 91123456789'; // Argentina
                                case '+55':
                                    return 'e.g. 11991234567'; // Brazil
                                case '+56':
                                    return 'e.g. 912345678'; // Chile
                                case '+57':
                                    return 'e.g. 3001234567'; // Colombia
                                case '+58':
                                    return 'e.g. 4121234567'; // Venezuela
                                case '+60':
                                    return 'e.g. 123456789'; // Malaysia
                                case '+61':
                                    return 'e.g. 412345678'; // Australia
                                case '+62':
                                    return 'e.g. 8123456789'; // Indonesia
                                case '+63':
                                    return 'e.g. 9171234567'; // Philippines
                                case '+64':
                                    return 'e.g. 211234567'; // New Zealand
                                case '+65':
                                    return 'e.g. 81234567'; // Singapore
                                case '+66':
                                    return 'e.g. 812345678'; // Thailand
                                case '+81':
                                    return 'e.g. 9012345678'; // Japan
                                case '+82':
                                    return 'e.g. 1023456789'; // South Korea
                                case '+84':
                                    return 'e.g. 912345678'; // Vietnam
                                case '+86':
                                    return 'e.g. 13123456789'; // China
                                case '+90':
                                    return 'e.g. 5012345678'; // Turkey
                                case '+91':
                                    return 'e.g. 9876543210'; // India
                                case '+92':
                                    return 'e.g. 3012345678'; // Pakistan
                                case '+93':
                                    return 'e.g. 701234567'; // Afghanistan
                                case '+94':
                                    return 'e.g. 711234567'; // Sri Lanka
                                case '+95':
                                    return 'e.g. 92123456'; // Myanmar
                                case '+98':
                                    return 'e.g. 9123456789'; // Iran
                                case '+212':
                                    return 'e.g. 612345678'; // Morocco
                                case '+213':
                                    return 'e.g. 551234567'; // Algeria
                                case '+216':
                                    return 'e.g. 20123456'; // Tunisia
                                case '+218':
                                    return 'e.g. 912345678'; // Libya
                                case '+220':
                                    return 'e.g. 3012345'; // Gambia
                                case '+221':
                                    return 'e.g. 701234567'; // Senegal
                                case '+222':
                                    return 'e.g. 21234567'; // Mauritania
                                case '+223':
                                    return 'e.g. 65123456'; // Mali
                                case '+224':
                                    return 'e.g. 621234567'; // Guinea
                                case '+225':
                                    return 'e.g. 01234567'; // Ivory Coast
                                case '+226':
                                    return 'e.g. 70123456'; // Burkina Faso
                                case '+227':
                                    return 'e.g. 90123456'; // Niger
                                case '+228':
                                    return 'e.g. 90123456'; // Togo
                                case '+229':
                                    return 'e.g. 90123456'; // Benin
                                case '+230':
                                    return 'e.g. 51234567'; // Mauritius
                                case '+231':
                                    return 'e.g. 771234567'; // Liberia
                                case '+232':
                                    return 'e.g. 25123456'; // Sierra Leone
                                case '+233':
                                    return 'e.g. 201234567'; // Ghana
                                case '+234':
                                    return 'e.g. 8021234567'; // Nigeria
                                case '+235':
                                    return 'e.g. 61234567'; // Chad
                                case '+236':
                                    return 'e.g. 70123456'; // Central African Republic
                                case '+237':
                                    return 'e.g. 671234567'; // Cameroon
                                case '+238':
                                    return 'e.g. 5012345'; // Cape Verde
                                case '+239':
                                    return 'e.g. 9812345'; // Sao Tome and Principe
                                case '+240':
                                    return 'e.g. 222123456'; // Equatorial Guinea
                                case '+241':
                                    return 'e.g. 06123456'; // Gabon
                                case '+242':
                                    return 'e.g. 051234567'; // Republic of the Congo
                                case '+243':
                                    return 'e.g. 812345678'; // DR Congo
                                case '+244':
                                    return 'e.g. 912345678'; // Angola
                                case '+245':
                                    return 'e.g. 9551234'; // Guinea-Bissau
                                case '+248':
                                    return 'e.g. 2512345'; // Seychelles
                                case '+249':
                                    return 'e.g. 911234567'; // Sudan
                                case '+250':
                                    return 'e.g. 721234567'; // Rwanda
                                case '+251':
                                    return 'e.g. 911234567'; // Ethiopia
                                case '+252':
                                    return 'e.g. 612345678'; // Somalia
                                case '+253':
                                    return 'e.g. 7712345'; // Djibouti
                                case '+254':
                                    return 'e.g. 712123456'; // Kenya
                                case '+255':
                                    return 'e.g. 621234567'; // Tanzania
                                case '+256':
                                    return 'e.g. 712345678'; // Uganda
                                case '+257':
                                    return 'e.g. 79123456'; // Burundi
                                case '+258':
                                    return 'e.g. 821234567'; // Mozambique
                                case '+260':
                                    return 'e.g. 955123456'; // Zambia
                                case '+261':
                                    return 'e.g. 321234567'; // Madagascar
                                case '+262':
                                    return 'e.g. 692123456'; // Reunion
                                case '+263':
                                    return 'e.g. 712345678'; // Zimbabwe
                                case '+264':
                                    return 'e.g. 811234567'; // Namibia
                                case '+265':
                                    return 'e.g. 991234567'; // Malawi
                                case '+266':
                                    return 'e.g. 50123456'; // Lesotho
                                case '+267':
                                    return 'e.g. 71123456'; // Botswana
                                case '+268':
                                    return 'e.g. 76123456'; // Eswatini
                                case '+269':
                                    return 'e.g. 3212345'; // Comoros
                                case '+290':
                                    return 'e.g. 51234'; // Saint Helena
                                case '+291':
                                    return 'e.g. 7123456'; // Eritrea
                                case '+297':
                                    return 'e.g. 5601234'; // Aruba
                                case '+298':
                                    return 'e.g. 221234'; // Faroe Islands
                                case '+299':
                                    return 'e.g. 221234'; // Greenland
                                case '+350':
                                    return 'e.g. 57123456'; // Gibraltar
                                case '+351':
                                    return 'e.g. 912345678'; // Portugal
                                case '+352':
                                    return 'e.g. 621123456'; // Luxembourg
                                case '+353':
                                    return 'e.g. 851234567'; // Ireland
                                case '+354':
                                    return 'e.g. 6612345'; // Iceland
                                case '+355':
                                    return 'e.g. 671234567'; // Albania
                                case '+356':
                                    return 'e.g. 96123456'; // Malta
                                case '+357':
                                    return 'e.g. 95123456'; // Cyprus
                                case '+358':
                                    return 'e.g. 412345678'; // Finland
                                case '+359':
                                    return 'e.g. 881234567'; // Bulgaria
                                case '+370':
                                    return 'e.g. 61234567'; // Lithuania
                                case '+371':
                                    return 'e.g. 21234567'; // Latvia
                                case '+372':
                                    return 'e.g. 5123456'; // Estonia
                                case '+373':
                                    return 'e.g. 62123456'; // Moldova
                                case '+374':
                                    return 'e.g. 77123456'; // Armenia
                                case '+375':
                                    return 'e.g. 291234567'; // Belarus
                                case '+376':
                                    return 'e.g. 312345'; // Andorra
                                case '+377':
                                    return 'e.g. 612345678'; // Monaco
                                case '+378':
                                    return 'e.g. 666612'; // San Marino
                                case '+379':
                                    return 'e.g. 06698'; // Vatican City
                                case '+380':
                                    return 'e.g. 501234567'; // Ukraine
                                case '+381':
                                    return 'e.g. 601234567'; // Serbia
                                case '+382':
                                    return 'e.g. 67123456'; // Montenegro
                                case '+383':
                                    return 'e.g. 44123456'; // Kosovo
                                case '+385':
                                    return 'e.g. 912345678'; // Croatia
                                case '+386':
                                    return 'e.g. 31123456'; // Slovenia
                                case '+387':
                                    return 'e.g. 61123456'; // Bosnia and Herzegovina
                                case '+389':
                                    return 'e.g. 70123456'; // North Macedonia
                                case '+420':
                                    return 'e.g. 601234567'; // Czech Republic
                                case '+421':
                                    return 'e.g. 901234567'; // Slovakia
                                case '+423':
                                    return 'e.g. 6612345'; // Liechtenstein
                                case '+500':
                                    return 'e.g. 51234'; // Falkland Islands
                                case '+501':
                                    return 'e.g. 6123456'; // Belize
                                case '+502':
                                    return 'e.g. 51234567'; // Guatemala
                                case '+503':
                                    return 'e.g. 71234567'; // El Salvador
                                case '+504':
                                    return 'e.g. 91234567'; // Honduras
                                case '+505':
                                    return 'e.g. 81234567'; // Nicaragua
                                case '+506':
                                    return 'e.g. 61234567'; // Costa Rica
                                case '+507':
                                    return 'e.g. 61234567'; // Panama
                                case '+508':
                                    return 'e.g. 551234'; // Saint Pierre and Miquelon
                                case '+509':
                                    return 'e.g. 34123456'; // Haiti
                                case '+590':
                                    return 'e.g. 690123456'; // Guadeloupe
                                case '+591':
                                    return 'e.g. 71234567'; // Bolivia
                                case '+592':
                                    return 'e.g. 6123456'; // Guyana
                                case '+593':
                                    return 'e.g. 991234567'; // Ecuador
                                case '+594':
                                    return 'e.g. 694201234'; // French Guiana
                                case '+595':
                                    return 'e.g. 961234567'; // Paraguay
                                case '+596':
                                    return 'e.g. 696123456'; // Martinique
                                case '+597':
                                    return 'e.g. 8123456'; // Suriname
                                case '+598':
                                    return 'e.g. 94123456'; // Uruguay
                                case '+599':
                                    return 'e.g. 9512345'; // Curaçao
                                case '+670':
                                    return 'e.g. 7712345'; // Timor-Leste
                                case '+672':
                                    return 'e.g. 381234'; // Norfolk Island
                                case '+673':
                                    return 'e.g. 7123456'; // Brunei
                                case '+674':
                                    return 'e.g. 5551234'; // Nauru
                                case '+675':
                                    return 'e.g. 70123456'; // Papua New Guinea
                                case '+676':
                                    return 'e.g. 7712345'; // Tonga
                                case '+677':
                                    return 'e.g. 7412345'; // Solomon Islands
                                case '+678':
                                    return 'e.g. 5912345'; // Vanuatu
                                case '+679':
                                    return 'e.g. 7012345'; // Fiji
                                case '+680':
                                    return 'e.g. 7712345'; // Palau
                                case '+681':
                                    return 'e.g. 501234'; // Wallis and Futuna
                                case '+682':
                                    return 'e.g. 55123'; // Cook Islands
                                case '+683':
                                    return 'e.g. 1234'; // Niue
                                case '+685':
                                    return 'e.g. 7212345'; // Samoa
                                case '+686':
                                    return 'e.g. 72012345'; // Kiribati
                                case '+687':
                                    return 'e.g. 751234'; // New Caledonia
                                case '+688':
                                    return 'e.g. 901234'; // Tuvalu
                                case '+689':
                                    return 'e.g. 87123456'; // French Polynesia
                                case '+690':
                                    return 'e.g. 1234'; // Tokelau
                                case '+691':
                                    return 'e.g. 3501234'; // Micronesia
                                case '+692':
                                    return 'e.g. 2351234'; // Marshall Islands
                                case '+850':
                                    return 'e.g. 1912345678'; // North Korea
                                case '+852':
                                    return 'e.g. 51234567'; // Hong Kong
                                case '+853':
                                    return 'e.g. 66123456'; // Macau
                                case '+855':
                                    return 'e.g. 91234567'; // Cambodia
                                case '+856':
                                    return 'e.g. 201234567'; // Laos
                                case '+880':
                                    return 'e.g. 1812345678'; // Bangladesh
                                case '+886':
                                    return 'e.g. 912345678'; // Taiwan
                                case '+960':
                                    return 'e.g. 7712345'; // Maldives
                                case '+961':
                                    return 'e.g. 71123456'; // Lebanon
                                case '+962':
                                    return 'e.g. 791234567'; // Jordan
                                case '+963':
                                    return 'e.g. 944567890'; // Syria
                                case '+964':
                                    return 'e.g. 7712345678'; // Iraq
                                case '+965':
                                    return 'e.g. 50012345'; // Kuwait
                                case '+966':
                                    return 'e.g. 501234567'; // Saudi Arabia
                                case '+967':
                                    return 'e.g. 712345678'; // Yemen
                                case '+968':
                                    return 'e.g. 91234567'; // Oman
                                case '+970':
                                    return 'e.g. 591234567'; // Palestine
                                case '+971':
                                    return 'e.g. 501234567'; // UAE
                                case '+972':
                                    return 'e.g. 501234567'; // Israel
                                case '+973':
                                    return 'e.g. 36123456'; // Bahrain
                                case '+974':
                                    return 'e.g. 33123456'; // Qatar
                                case '+975':
                                    return 'e.g. 17123456'; // Bhutan
                                case '+976':
                                    return 'e.g. 88123456'; // Mongolia
                                case '+977':
                                    return 'e.g. 9841234567'; // Nepal
                                case '+992':
                                    return 'e.g. 917123456'; // Tajikistan
                                case '+993':
                                    return 'e.g. 61234567'; // Turkmenistan
                                case '+994':
                                    return 'e.g. 401234567'; // Azerbaijan
                                case '+995':
                                    return 'e.g. 551123456'; // Georgia
                                case '+996':
                                    return 'e.g. 700123456'; // Kyrgyzstan
                                case '+998':
                                    return 'e.g. 912345678'; // Uzbekistan
                                default:
                                    return 'Enter mobile number';
                            }
                        })
                        ->prefix(function ($get) {
                            return $get('country_code') ?? '+62';
                        })
                        ->rules([
                            'required',
                            'numeric',
                            function ($get) {
                                return function (string $attribute, $value, Closure $fail) use ($get) {
                                    $countryCode = $get('country_code') ?? '+62';
                                    $cleanNumber = preg_replace('/[^0-9]/', '', $value);

                                    // Remove country code if accidentally included
                                    $cleanNumber = str_replace(preg_replace('/[^0-9]/', '', $countryCode), '', $cleanNumber);

                                    // Common validations
                                    if (empty($cleanNumber)) {
                                        $fail('Please enter a valid mobile number.');

                                        return;
                                    }

                                    // Country-specific validations
                                    switch ($countryCode) {
                                        case '+1': // USA/Canada
                                            if (! preg_match('/^[2-9]\d{9}$/', $cleanNumber)) {
                                                $fail('US/Canada numbers must be 10 digits starting with 2-9 (excluding 1).');
                                            }
                                            break;

                                        case '+44': // UK
                                            if (! preg_match('/^7\d{9}$/', $cleanNumber)) {
                                                $fail('UK mobile numbers must be 10 digits starting with 7.');
                                            }
                                            break;

                                        case '+62': // Indonesia
                                            if (! preg_match('/^8\d{7,11}$/', $cleanNumber)) {
                                                $fail('Indonesian numbers must start with 8 and be 8-12 digits long.');
                                            }
                                            break;

                                        case '+60': // Malaysia
                                            if (! preg_match('/^1\d{8,9}$/', $cleanNumber)) {
                                                $fail('Malaysian numbers must start with 1 and be 9-10 digits long.');
                                            }
                                            break;

                                        case '+65': // Singapore
                                            if (! preg_match('/^[89]\d{7}$/', $cleanNumber)) {
                                                $fail('Singapore numbers must be 8 digits starting with 8 or 9.');
                                            }
                                            break;

                                        case '+86': // China
                                            if (! preg_match('/^1\d{10}$/', $cleanNumber)) {
                                                $fail('Chinese numbers must be 11 digits starting with 1.');
                                            }
                                            break;

                                        case '+91': // India
                                            if (! preg_match('/^[6-9]\d{9}$/', $cleanNumber)) {
                                                $fail('Indian numbers must be 10 digits starting with 6-9.');
                                            }
                                            break;

                                        case '+81': // Japan
                                            if (! preg_match('/^[789]\d{8}$/', $cleanNumber)) {
                                                $fail('Japanese mobile numbers must be 9 digits starting with 7, 8 or 9.');
                                            }
                                            break;

                                        case '+82': // South Korea
                                            if (! preg_match('/^1\d{8,9}$/', $cleanNumber)) {
                                                $fail('South Korean numbers must be 9-10 digits starting with 1.');
                                            }
                                            break;

                                        case '+84': // Vietnam
                                            if (! preg_match('/^[3-9]\d{7,8}$/', $cleanNumber)) {
                                                $fail('Vietnamese numbers must be 8-9 digits starting with 3-9.');
                                            }
                                            break;

                                        case '+63': // Philippines
                                            if (! preg_match('/^[9]\d{9}$/', $cleanNumber)) {
                                                $fail('Philippine numbers must be 10 digits starting with 9.');
                                            }
                                            break;

                                        case '+66': // Thailand
                                            if (! preg_match('/^[689]\d{8}$/', $cleanNumber)) {
                                                $fail('Thai numbers must be 9 digits starting with 6, 8 or 9.');
                                            }
                                            break;

                                            // Add more country-specific validations as needed

                                        default:
                                            // Generic validation for other countries
                                            if (strlen($cleanNumber) < 5 || strlen($cleanNumber) > 15) {
                                                $fail('Please enter a valid mobile number (5-15 digits after country code).');
                                            }
                                            break;
                                    }
                                };
                            },
                        ])
                        ->columnSpan(1),
                    Forms\Components\TextInput::make('Email')
                        ->required()
                        ->email()
                        ->live(onBlur: true) // Validate when field loses focus
                        ->rules([
                            function () {
                                return function (string $attribute, $value, Closure $fail) {
                                    // Basic @ check
                                    if (! str_contains($value, '@')) {
                                        $fail('Please include an \'@\' in the email address. Example: name@example.com');

                                        return;
                                    }

                                    // Full email validation
                                    if (! filter_var($value, FILTER_VALIDATE_EMAIL)) {
                                        $fail('Please enter a valid email address (e.g. name@example.com)');

                                        return;
                                    }

                                    // Domain check
                                    $domain = substr(strrchr($value, '@'), 1);
                                    if (! checkdnsrr($domain, 'MX')) {
                                        $fail('We couldn\'t verify this email domain exists');
                                    }
                                };
                            },
                        ])
                        ->validationMessages([
                            'required' => 'Email address is required',
                        ])
                        ->columnSpan(2)
                        ->hintColor('danger'), // Makes error messages more visible
                ]),
            // Forms\Components\TextInput::make('Email')
            //     ->required()
            //     ->email()
            //     ->placeholder('your.email@example.com')
            //     // ->helperText('Example: name@example.com')
            //     ->rules([
            //         'required',
            //         'email:rfc,dns',
            //         'max:255',
            //         function () {
            //             return function (string $attribute, $value, Closure $fail) {
            //                 // Split email into local part and domain
            //                 $parts = explode('@', $value);

            //                 // 1. Basic structure validation
            //                 if (count($parts) !== 2) {
            //                     $fail('Email must contain exactly one @ symbol. Example: name@domain.com');
            //                     return;
            //                 }

            //                 [$localPart, $domain] = $parts;

            //                 // 2. Local part validation (before @)
            //                 if (empty($localPart)) {
            //                     $fail('The part before @ cannot be empty. Example: name@domain.com');
            //                     return;
            //                 }

            //                 if (strlen($localPart) > 64) {
            //                     $fail('The part before @ cannot exceed 64 characters.');
            //                     return;
            //                 }

            //                 if (preg_match('/[\/\\\|\"\,\:\;\<\>\s]/', $localPart)) {
            //                     $fail('The part before @ contains invalid characters (no spaces or special chars like /\\|",:;<>).');
            //                     return;
            //                 }

            //                 if (preg_match('/\.{2,}/', $localPart)) {
            //                     $fail('The part before @ cannot contain consecutive dots (..).');
            //                     return;
            //                 }

            //                 if (preg_match('/^\.|\.$/', $localPart)) {
            //                     $fail('The part before @ cannot start or end with a dot.');
            //                     return;
            //                 }

            //                 // 3. Domain validation (after @)
            //                 if (empty($domain)) {
            //                     $fail('The domain part after @ cannot be empty. Example: name@domain.com');
            //                     return;
            //                 }

            //                 if (strlen($domain) > 255) {
            //                     $fail('The domain part is too long (max 255 characters).');
            //                     return;
            //                 }

            //                 if (!preg_match('/^[a-z0-9\-\.]+$/i', $domain)) {
            //                     $fail('Domain can only contain letters, numbers, dots and hyphens.');
            //                     return;
            //                 }

            //                 if (preg_match('/\.{2,}/', $domain)) {
            //                     $fail('Domain cannot contain consecutive dots (..).');
            //                     return;
            //                 }

            //                 if (preg_match('/^\-|\.\-|\-$/', $domain)) {
            //                     $fail('Domain cannot start/end with hyphen or have hyphen after dot.');
            //                     return;
            //                 }

            //                 // 4. Top-level domain validation
            //                 if (!preg_match('/\.[a-z]{2,}$/i', $domain)) {
            //                     $fail('Domain must have a valid extension (like .com, .co.id).');
            //                     return;
            //                 }

            //                 // 5. MX record check
            //                 if (!checkdnsrr($domain, 'MX')) {
            //                     $fail('The email domain does not exist or cannot receive emails.');
            //                     return;
            //                 }

            //                 // 6. Country-specific suggestions (optional)
            //                 // $countryCode = $this->data['country_code'] ?? '+62';
            //                 // if ($countryCode === '+62' && !preg_match('/\.id$|gmail\.co\.id$|yahoo\.co\.id$/i', $domain)) {
            //                 //     $fail('For Indonesia, we recommend using .id domains like example.co.id');
            //                 // }
            //             };
            //         }
            //     ])
            //     ->columnSpan(2),
            // ]),
            Forms\Components\Section::make('Address Information')
                ->columns(2)
                ->schema([
                    Forms\Components\TextInput::make('Street')
                        ->placeholder('123 Main Street'),

                    Forms\Components\TextInput::make('City')
                        ->placeholder('Jakarta'),

                    Forms\Components\TextInput::make('Country')
                        ->placeholder('Indonesia'),

                    Forms\Components\TextInput::make('ZipCode')
                        ->placeholder('12345'),

                    Forms\Components\TextInput::make('State')
                        ->placeholder('DKI Jakarta'),
                ]),
            Forms\Components\Section::make('Professional Details')
                ->columns(2)
                ->schema([
                    Forms\Components\TextInput::make('CurrentEmployer')
                        ->label('Current Employer (Company Name)')
                        ->placeholder('Alphabet Inc.'),
                    Forms\Components\TextInput::make('CurrentJobTitle')
                        ->label('Current Job Title')
                        ->placeholder('Software Engineer'),
                    Forms\Components\Select::make('experience')
                        ->options([
                            '1year' => '1 Year',
                            '2year' => '2 Years',
                            '3year' => '3 Years',
                            '4year' => '4 Years',
                            '5year' => '5 Years',
                            '6year' => '6 Years',
                            '7year' => '7 Years',
                            '8year' => '8 Years',
                            '9year' => '9 Years',
                            '10year+' => '10 Years & Above',
                        ])
                        ->label('Experience'),
                ]),
            Forms\Components\Section::make('Educational Details')
                ->schema([
                    Forms\Components\Repeater::make('School')
                        ->label('')
                        ->addActionLabel('+ Add Degree Information')
                        ->schema([
                            Forms\Components\TextInput::make('school_name')
                                ->required()
                                ->placeholder('President University'),
                            Forms\Components\TextInput::make('major')
                                ->required()
                                ->placeholder('Computer Science'),
                            Forms\Components\Select::make('duration')
                                ->options([
                                    '3years' => '3 Years',
                                    '4years' => '4 Years',
                                    '5years' => '5 Years',
                                ])
                                ->required(),
                            Forms\Components\Checkbox::make('pursuing')
                                ->label('Pursuing')
                                ->live()
                                ->inline(false)
                                ->extraAttributes([
                                    'style' => 'cursor: pointer;',
                                    'wire:key' => 'pursuing-checkbox',
                                ]),
                        ])
                        ->deletable(true)
                        ->columns(4),
                ]),
            Forms\Components\Section::make('Experience Details')
                ->schema([
                    Forms\Components\Repeater::make('ExperienceDetails')
                        ->label('')
                        ->addActionLabel('Add Experience Details')
                        ->schema([
                            Forms\Components\Checkbox::make('current')
                                ->label('Current?')
                                ->live()
                                ->inline(false)
                                ->extraAttributes([
                                    'style' => 'cursor: pointer;',
                                    'wire:key' => 'current-checkbox',
                                ]),
                            Forms\Components\TextInput::make('company_name')
                                ->placeholder('Alphabet Inc.'),
                            Forms\Components\Select::make('duration')
                                ->options([
                                    '1year' => '1 Year',
                                    '2year' => '2 Years',
                                    '3year' => '3 Years',
                                    '4year' => '4 Years',
                                    '5year' => '5 Years',
                                    '6year' => '6 Years',
                                    '7year' => '7 Years',
                                    '8year' => '8 Years',
                                    '9year' => '9 Years',
                                    '10year+' => '10 Years & Above',
                                ])
                                ->required()
                                ->label('Duration'),
                            Forms\Components\TextInput::make('role')
                                ->placeholder('Software Engineer'),
                            Forms\Components\Textarea::make('company_address')
                                ->placeholder('Mountain View, California, United States'),
                        ])
                        ->deletable(true)
                        ->columns(5),
                ]),
            Forms\Components\FileUpload::make('attachment')
                ->preserveFilenames(false) // Changed from true to false to allow filename modification
                ->directory('JobCandidate-attachments')
                ->visibility('private')
                ->openable()
                ->downloadable()
                ->previewable()
                ->acceptedFileTypes([
                    'application/pdf',
                ])
                ->required()
                ->label('Resume')
                ->getUploadedFileNameForStorageUsing(
                    function (TemporaryUploadedFile $file): string {
                        // Generate a unique filename with original extension
                        $originalName = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
                        $extension = $file->getClientOriginalExtension();
                        $uniqueName = $originalName.'_'.uniqid().'.'.$extension;

                        return $uniqueName;
                    }
                ),
        ];
    }

    // private function captchaField(): array
    // {
    //     if (! config('recruit.enable_captcha')) {
    //         return [];
    //     }
    //     if (config('recruit.enable_captcha')) {
    //         if (config('recruit.captcha_provider.default') === 'Google') {
    //             return [GRecaptcha::make('captcha')];
    //         }
    //         if (config('recruit.captcha_provider.default') === 'Cloudflare') {
    //             return [
    //                 Turnstile::make('turnstile')
    //                     ->theme('light')
    //                     ->size('normal')
    //                     ->language('en-US'),
    //             ];
    //         }

    //         // default
    //         if (config('recruit.captcha_provider.default') === 'Recruit_Captcha') {
    //             return [
    //                 Captcha::make('captcha')
    //                     ->rules(['captcha'])
    //                     ->required()
    //                     ->validationMessages([
    //                         'captcha' => __('Captcha does not match the image'),
    //                     ]),
    //             ];
    //         }

    //     }

    //     return [];

    // }

    #[Title('Apply Job ')]
    public function render()
    {
        return view('livewire.career-apply-job', [
            'jobDetail' => $this->record,
        ]);
    }
}
