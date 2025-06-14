<?php

namespace App\Filament\Enums;

enum RequiredSkill: string
{
    // Technology & IT
    case projectManagement = 'Project Management';
    case webDevelopment = 'Web Development';
    case softwareEngineering = 'Software Engineering';
    case mobileDevelopment = 'Mobile Development';
    case frontendDevelopment = 'Frontend Development';
    case backendDevelopment = 'Backend Development';
    case fullstackDevelopment = 'Fullstack Development';
    case devops = 'DevOps';
    case cloudComputing = 'Cloud Computing';
    case cybersecurity = 'Cybersecurity';
    case dataScience = 'Data Science';
    case machineLearning = 'Machine Learning';
    case artificialIntelligence = 'Artificial Intelligence';
    case blockchain = 'Blockchain';
    case iot = 'IoT';
    case arVr = 'AR/VR';
    case gameDevelopment = 'Game Development';
    case qaTesting = 'QA Testing';
    case uiDesign = 'UI Design';
    case uxDesign = 'UX Design';
    case graphicDesign = 'Graphic Design';
    case animation = 'Animation';
    case threeDModeling = '3D Modeling';
    case networkEngineering = 'Network Engineering';
    case systemAdministration = 'System Administration';
    case databaseAdministration = 'Database Administration';
    case itSupport = 'IT Support';
    case technicalSupport = 'Technical Support';

    // Business & Management
    case productManagement = 'Product Management';
    case businessAnalysis = 'Business Analysis';
    case businessIntelligence = 'Business Intelligence';
    case digitalTransformation = 'Digital Transformation';
    case changeManagement = 'Change Management';
    case riskManagement = 'Risk Management';
    case agileMethodology = 'Agile Methodology';
    case scrum = 'Scrum';
    case kanban = 'Kanban';
    case leanManagement = 'Lean Management';
    case sixSigma = 'Six Sigma';
    case processImprovement = 'Process Improvement';

    // Marketing & Creative
    case digitalMarketing = 'Digital Marketing';
    case seo = 'SEO';
    case sem = 'SEM';
    case ppc = 'PPC';
    case socialMediaMarketing = 'Social Media Marketing';
    case contentMarketing = 'Content Marketing';
    case emailMarketing = 'Email Marketing';
    case influencerMarketing = 'Influencer Marketing';
    case brandManagement = 'Brand Management';
    case marketResearch = 'Market Research';
    case copywriting = 'Copywriting';
    case technicalWriting = 'Technical Writing';
    case creativeWriting = 'Creative Writing';
    case videoProduction = 'Video Production';
    case photography = 'Photography';
    case illustration = 'Illustration';

    // Finance & Accounting
    case financialAnalysis = 'Financial Analysis';
    case accounting = 'Accounting';
    case auditing = 'Auditing';
    case bookkeeping = 'Bookkeeping';
    case taxPreparation = 'Tax Preparation';
    case financialPlanning = 'Financial Planning';
    case investmentAnalysis = 'Investment Analysis';
    case riskAssessment = 'Risk Assessment';
    case payrollManagement = 'Payroll Management';

    // Healthcare
    case nursing = 'Nursing';
    case medicalCoding = 'Medical Coding';
    case healthcareAdministration = 'Healthcare Administration';
    case pharmaceuticalSales = 'Pharmaceutical Sales';
    case medicalWriting = 'Medical Writing';
    case clinicalResearch = 'Clinical Research';

    // Engineering
    case civilEngineering = 'Civil Engineering';
    case mechanicalEngineering = 'Mechanical Engineering';
    case electricalEngineering = 'Electrical Engineering';
    case chemicalEngineering = 'Chemical Engineering';
    case aerospaceEngineering = 'Aerospace Engineering';
    case automotiveEngineering = 'Automotive Engineering';
    case industrialEngineering = 'Industrial Engineering';

    // Other Professional
    case humanResources = 'Human Resources';
    case recruitment = 'Recruitment';
    case trainingDevelopment = 'Training Development';
    case customerService = 'Customer Service';
    case sales = 'Sales';
    case businessDevelopment = 'Business Development';
    case supplyChain = 'Supply Chain';
    case logistics = 'Logistics';
    case procurement = 'Procurement';
    case legalResearch = 'Legal Research';
    case contractManagement = 'Contract Management';
    case translation = 'Translation';
    case interpretation = 'Interpretation';

    // Emerging Technologies
    case generativeAi = 'Generative AI';
    case promptEngineering = 'Prompt Engineering';
    case quantumComputing = 'Quantum Computing';
    case robotics = 'Robotics';
    case computerVision = 'Computer Vision';
    case nlp = 'NLP';

    public static function toArray(): array
    {
        return array_column(self::cases(), 'value', 'value');
    }
}