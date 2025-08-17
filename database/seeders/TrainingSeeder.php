<?php

namespace Database\Seeders;

use App\Models\JobOpenings;
use App\Models\Package;
use App\Models\Question;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TrainingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->warn(PHP_EOL.'Creating and Assigning Trainings (Packages)...');

        $trainingsData = $this->getTrainingsData();

        foreach ($trainingsData as $data) {
            // 1. Create the Package
            $package = Package::create($data['package']);
            $this->command->info("Created Package: {$package->name}");

            // 2. Create Questions and Options, then attach to the Package
            foreach ($data['questions'] as $qData) {
                $question = Question::create($qData['details']);
                $question->options()->createMany($qData['options']);

                DB::table('package_questions')->insert([
                    'package_id' => $package->id,
                    'question_id' => $question->id,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
            $this->command->info('-> Attached '.count($data['questions']).' questions.');

            // 3. Find the corresponding Job Opening and attach the Package
            $jobOpening = JobOpenings::where('JobTitle', $data['jobTitle'])->first();
            if ($jobOpening) {
                // This requires a `belongsToMany` relationship named `packages` on the JobOpening model.
                DB::table('job_opening_package')->insert([
                    'job_opening_id' => $jobOpening->id,
                    'package_id' => $package->id,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
                $this->command->info("-> Associated with Job Opening: {$jobOpening->JobTitle}");
            }
        }

        $this->command->info('Trainings created and assigned successfully.');
    }

    private function getTrainingsData(): array
    {
        return [
            [
                'department' => 'Investment Banking',
                'jobTitle' => 'Investment Banking Analyst',
                'package' => ['name' => 'Investment Banking Fundamentals', 'duration' => 120],
                'questions' => [
                    [
                        'details' => ['question' => 'What is the primary purpose of a Discounted Cash Flow (DCF) analysis?', 'explanation' => 'DCF is a valuation method that estimates an investment\'s value based on its expected future cash flows.'],
                        'options' => [['option_text' => 'To determine intrinsic value', 'score' => 10], ['option_text' => 'To calculate historical profit', 'score' => 0], ['option_text' => 'To assess market sentiment', 'score' => 0], ['option_text' => 'To measure liquidity ratio', 'score' => 0]],
                    ],
                    [
                        'details' => ['question' => 'In an M&A context, what does "synergy" refer to?', 'explanation' => 'Synergy is the concept that the combined value and performance of two companies will be greater than the sum of the separate individual parts.'],
                        'options' => [['option_text' => 'The cost of the acquisition', 'score' => 0], ['option_text' => 'The potential financial benefit achieved by combining companies', 'score' => 10], ['option_text' => 'The legal fees involved in the merger', 'score' => 0], ['option_text' => 'The employee severance packages', 'score' => 0]],
                    ],
                    [
                        'details' => ['question' => 'What is a Leveraged Buyout (LBO)?', 'explanation' => 'An LBO is the acquisition of another company using a significant amount of borrowed money (leverage) to meet the cost of acquisition.'],
                        'options' => [['option_text' => 'An acquisition using only company equity', 'score' => 0], ['option_text' => 'A merger of two equally sized companies', 'score' => 0], ['option_text' => 'An acquisition financed heavily with debt', 'score' => 10], ['option_text' => 'A government-funded bailout', 'score' => 0]],
                    ],
                    [
                        'details' => ['question' => 'Which document is filed to the OJK (regulator) to initiate an IPO in Indonesia?', 'explanation' => 'The "Prospektus Awal" or preliminary prospectus is the key registration document submitted for an IPO.'],
                        'options' => [['option_text' => 'Annual Report', 'score' => 0], ['option_text' => 'Prospektus Awal (Preliminary Prospectus)', 'score' => 10], ['option_text' => 'Company Bylaws', 'score' => 0], ['option_text' => 'Tax Return', 'score' => 0]],
                    ],
                    [
                        'details' => ['question' => 'What are "deal toys" in investment banking?', 'explanation' => 'Deal toys are customized trophies or mementos given to bankers and clients to commemorate the closing of a major financial transaction.'],
                        'options' => [['option_text' => 'Financial modeling software', 'score' => 0], ['option_text' => 'Commemorative trophies for a closed deal', 'score' => 10], ['option_text' => 'A type of high-risk bond', 'score' => 0], ['option_text' => 'A signing bonus for new hires', 'score' => 0]],
                    ],
                ],
            ],
            [
                'department' => 'Equity Research',
                'jobTitle' => 'Equity Research Associate',
                'package' => ['name' => 'Equity Research & Valuation', 'duration' => 120],
                'questions' => [
                    [
                        'details' => ['question' => 'What does the P/E ratio measure?', 'explanation' => 'The Price-to-Earnings (P/E) ratio is a valuation multiple that compares a company\'s share price to its earnings per share.'],
                        'options' => [['option_text' => 'A company\'s debt level', 'score' => 0], ['option_text' => 'How much investors are willing to pay per dollar of earnings', 'score' => 10], ['option_text' => 'The company\'s dividend yield', 'score' => 0], ['option_text' => 'The company\'s total revenue', 'score' => 0]],
                    ],
                    [
                        'details' => ['question' => 'A "Buy" rating on a stock typically means the analyst expects the stock to:', 'explanation' => 'A "Buy" rating indicates that the analyst believes the stock will outperform its sector or the overall market.'],
                        'options' => [['option_text' => 'Outperform the market', 'score' => 10], ['option_text' => 'Perform in line with the market', 'score' => 0], ['option_text' => 'Underperform the market', 'score' => 0], ['option_text' => 'Remain stable with no growth', 'score' => 0]],
                    ],
                    [
                        'details' => ['question' => 'What is a "moat" in the context of company analysis?', 'explanation' => 'A "moat" refers to a company\'s sustainable competitive advantage that protects its market share and profitability from competitors.'],
                        'options' => [['option_text' => 'The company\'s physical headquarters', 'score' => 0], ['option_text' => 'A sustainable competitive advantage', 'score' => 10], ['option_text' => 'The amount of cash on its balance sheet', 'score' => 0], ['option_text' => 'A recent marketing campaign', 'score' => 0]],
                    ],
                    [
                        'details' => ['question' => 'What is the difference between top-down and bottom-up analysis?', 'explanation' => 'Top-down starts with macroeconomic factors, while bottom-up focuses on individual company fundamentals first.'],
                        'options' => [['option_text' => 'Top-down focuses on individual stocks first', 'score' => 0], ['option_text' => 'Bottom-up starts with the overall economy', 'score' => 0], ['option_text' => 'Top-down starts with macroeconomic analysis, bottom-up starts with company specifics', 'score' => 10], ['option_text' => 'There is no difference', 'score' => 0]],
                    ],
                    [
                        'details' => ['question' => 'What does EV/EBITDA stand for?', 'explanation' => 'Enterprise Value to Earnings Before Interest, Taxes, Depreciation, and Amortization. It\'s a key valuation metric.'],
                        'options' => [['option_text' => 'Equity Value / Earnings Before Income Tax', 'score' => 0], ['option_text' => 'Enterprise Value / Earnings Before Interest, Taxes, Depreciation, and Amortization', 'score' => 10], ['option_text' => 'Estimated Value / Earning Before Income Tax & Dividends', 'score' => 0], ['option_text' => 'Economic Value / Estimated Business Income', 'score' => 0]],
                    ],
                ],
            ],
            [
                'department' => 'Compliance',
                'jobTitle' => 'Compliance Officer',
                'package' => ['name' => 'Financial Compliance & Regulation', 'duration' => 90],
                'questions' => [
                    [
                        'details' => ['question' => 'What is the primary goal of KYC procedures?', 'explanation' => 'Know Your Customer (KYC) procedures are designed to verify the identity of clients to prevent identity theft, financial fraud, and money laundering.'],
                        'options' => [['option_text' => 'To assess a client\'s credit score', 'score' => 0], ['option_text' => 'To prevent illegal activities like money laundering', 'score' => 10], ['option_text' => 'To market new products to clients', 'score' => 0], ['option_text' => 'To determine a client\'s investment risk tolerance', 'score' => 0]],
                    ],
                    [
                        'details' => ['question' => 'What does AML stand for?', 'explanation' => 'AML stands for Anti-Money Laundering, which refers to a set of laws and regulations intended to prevent criminals from disguising illegally obtained funds as legitimate income.'],
                        'options' => [['option_text' => 'Asset Management Legitimacy', 'score' => 0], ['option_text' => 'Anti-Market Leverage', 'score' => 0], ['option_text' => 'Anti-Money Laundering', 'score' => 10], ['option_text' => 'Asset Marketing & Logistics', 'score' => 0]],
                    ],
                    [
                        'details' => ['question' => 'What constitutes "insider trading"?', 'explanation' => 'Insider trading is the illegal practice of trading on the stock exchange to one\'s own advantage through having access to confidential, material non-public information.'],
                        'options' => [['option_text' => 'Trading based on public news reports', 'score' => 0], ['option_text' => 'Trading using material, non-public information', 'score' => 10], ['option_text' => 'Making a large volume of trades in a single day', 'score' => 0], ['option_text' => 'Trading stocks of the company you work for (with restrictions)', 'score' => 0]],
                    ],
                    [
                        'details' => ['question' => 'Who is the primary financial services regulator in Indonesia?', 'explanation' => 'The Otoritas Jasa Keuangan (OJK) is the Financial Services Authority of Indonesia.'],
                        'options' => [['option_text' => 'Bank Indonesia (BI)', 'score' => 0], ['option_text' => 'Kementerian Keuangan (Ministry of Finance)', 'score' => 0], ['option_text' => 'Otoritas Jasa Keuangan (OJK)', 'score' => 10], ['option_text' => 'Bursa Efek Indonesia (IDX)', 'score' => 0]],
                    ],
                    [
                        'details' => ['question' => 'What is a "Chinese Wall" in a financial institution?', 'explanation' => 'A "Chinese Wall" is an information barrier implemented to prevent exchanges of information between departments that could lead to conflicts of interest, such as between investment banking and equity research.'],
                        'options' => [['option_text' => 'A physical wall in the office', 'score' => 0], ['option_text' => 'An information barrier between departments to prevent conflicts of interest', 'score' => 10], ['option_text' => 'The main firewall for the company\'s network', 'score' => 0], ['option_text' => 'A list of restricted trading stocks', 'score' => 0]],
                    ],
                ],
            ],
            [
                'department' => 'Information Technology',
                'jobTitle' => 'Lead Cybersecurity Engineer',
                'package' => ['name' => 'Financial Technology & Security', 'duration' => 60],
                'questions' => [
                    [
                        'details' => ['question' => 'What is a form of malware designed to encrypt files, rendering them unusable. The malicious actor then demands a ransom in exchange for the encryption key?', 'explanation' => 'Ransomware'],
                        'options' => [['option_text' => 'Tupperware', 'score' => 0], ['option_text' => 'Spam', 'score' => 0], ['option_text' => 'Phishing', 'score' => 0], ['option_text' => 'Ransomware', 'score' => 10]],
                    ],
                    [
                        'details' => ['question' => 'If we are worried about forgetting the password, then here are the right steps to take', 'explanation' => 'Stored in a password manager application with fairly good protection'],
                        'options' => [['option_text' => 'Stored in a password manager application with fairly good protection', 'score' => 10], ['option_text' => 'Saved in the Notepad application and placed in the My Documents folder', 'score' => 0], ['option_text' => 'Written on a sticky note and then stuck on the work desk area', 'score' => 0], ['option_text' => 'Save it in the Excel application then give the file a name that is not related to the password', 'score' => 0]],
                    ],
                    [
                        'details' => ['question' => 'If we receive an e-mail from someone we do not know and it contains an attachment, what we should do is', 'explanation' => 'Delete the email'],
                        'options' => [['option_text' => 'Open the attachment to confirm its contents', 'score' => 0], ['option_text' => 'Forward the email to other colleagues so they can open the attachment first so they can it is guaranteed safe when we open it ourselves', 'score' => 0], ['option_text' => 'Forward the email to our personal email account so we can open the attachment at home', 'score' => 0], ['option_text' => 'Delete the email', 'score' => 10]],
                    ],
                    [
                        'details' => ['question' => 'Which of the following is a strong password?', 'explanation' => 'Ind0N3$!@@'],
                        'options' => [['option_text' => 'Password123', 'score' => 0], ['option_text' => 'Ind0N3$!@@', 'score' => 10], ['option_text' => 'XnmnX', 'score' => 0], ['option_text' => 'Password', 'score' => 0]],
                    ],
                    [
                        'details' => ['question' => 'Protecting data and information from unauthorized persons is the definition of?', 'explanation' => 'Confidentiality'],
                        'options' => [['option_text' => 'Confidentiality', 'score' => 10], ['option_text' => 'Compatibility', 'score' => 0], ['option_text' => 'Integrity', 'score' => 0], ['option_text' => 'Availability', 'score' => 0]],
                    ],
                    [
                        'details' => ['question' => 'Protecting the availability of organizational data and information, so that data is available when needed, is the meaning of?', 'explanation' => 'Availability'],
                        'options' => [['option_text' => 'Confidentiality', 'score' => 0], ['option_text' => 'Availability', 'score' => 10], ['option_text' => 'Integrity', 'score' => 0], ['option_text' => 'Compatibility', 'score' => 0]],
                    ],
                    [
                        'details' => ['question' => 'Protecting the integrity of organizational data and information from unauthorized modification is the meaning of?', 'explanation' => 'Integrity'],
                        'options' => [['option_text' => 'Confidentiality', 'score' => 0], ['option_text' => 'Availability', 'score' => 0], ['option_text' => 'Integrity', 'score' => 10], ['option_text' => 'Compatibility', 'score' => 0]],
                    ],
                    [
                        'details' => ['question' => 'What is the illegal act of stealing account credentials and access to devices by sending messages designed to trick recipients into clicking on a link or downloading an attached file called?', 'explanation' => 'Phishing'],
                        'options' => [['option_text' => 'System', 'score' => 0], ['option_text' => 'Ransomware', 'score' => 0], ['option_text' => 'Phishing', 'score' => 10], ['option_text' => 'Virus', 'score' => 0]],
                    ],
                    [
                        'details' => ['question' => 'What are the three main elements of system security?', 'explanation' => 'Confidentiality, Integrity and Availability'],
                        'options' => [['option_text' => 'Integrity, Possibility and Availability', 'score' => 0], ['option_text' => 'Association, Integrity and Availability', 'score' => 0], ['option_text' => 'Association, Integrity and Possibility', 'score' => 0], ['option_text' => 'Confidentiality, Integrity and Availability', 'score' => 10]],
                    ],
                    [
                        'details' => ['question' => 'The purpose of Security Awareness training is', 'explanation' => 'So that all individuals at BNIS understand the importance of information security and their responsibilities regarding that information security.'],
                        'options' => [['option_text' => 'To be proud', 'score' => 0], ['option_text' => 'So that all individuals at BNIS understand the importance of information security and their responsibilities regarding that information security.', 'score' => 10], ['option_text' => 'To Show Off', 'score' => 0], ['option_text' => 'Because information security is only the responsibility of the IT Team', 'score' => 0]],
                    ],
                ],
            ],
            // Add more departments like Sales & Trading and Wealth Management here following the same structure
        ];
    }
}
