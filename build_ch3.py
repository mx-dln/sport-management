from docx import Document
from docx.shared import Inches, Pt, RGBColor
from docx.enum.text import WD_ALIGN_PARAGRAPH
from docx.enum.table import WD_TABLE_ALIGNMENT, WD_CELL_VERTICAL_ALIGNMENT
from docx.oxml import OxmlElement
from docx.oxml.ns import qn
from pathlib import Path

out=Path(r'C:\Users\micha\OneDrive\Documents\Sites\sports-management\Capstone-Project-Chapter-III_Revised.docx')
d=Document()
s=d.sections[0]; s.top_margin=Inches(.8); s.bottom_margin=Inches(.75); s.left_margin=Inches(1); s.right_margin=Inches(1)
for st in ['Normal','Title','Heading 1','Heading 2','Heading 3']:
 d.styles[st].font.name='Times New Roman'; d.styles[st]._element.rPr.rFonts.set(qn('w:eastAsia'),'Times New Roman')
d.styles['Normal'].font.size=Pt(12); d.styles['Normal'].paragraph_format.line_spacing=1.5
d.styles['Title'].font.size=Pt(16); d.styles['Title'].font.bold=True
d.styles['Heading 1'].font.size=Pt(14); d.styles['Heading 1'].font.bold=True
d.styles['Heading 2'].font.size=Pt(12); d.styles['Heading 2'].font.bold=True

def p(txt='', bold=False, center=False):
 x=d.add_paragraph(); x.paragraph_format.first_line_indent=Inches(.5); x.paragraph_format.space_after=Pt(6)
 if center: x.alignment=WD_ALIGN_PARAGRAPH.CENTER; x.paragraph_format.first_line_indent=Inches(0)
 r=x.add_run(txt); r.bold=bold; return x

def heading(txt,l=1): d.add_heading(txt,l)
def bullet(txt):
 x=d.add_paragraph(style='List Bullet'); x.paragraph_format.line_spacing=1.3; x.add_run(txt)
def numbered(txt):
 x=d.add_paragraph(style='List Number'); x.paragraph_format.line_spacing=1.3; x.add_run(txt)
def prompt(txt):
 x=d.add_paragraph(); x.paragraph_format.left_indent=Inches(.3); x.paragraph_format.right_indent=Inches(.3); x.paragraph_format.space_after=Pt(8)
 r=x.add_run(txt); r.font.name='Consolas'; r.font.size=Pt(9); r.font.color.rgb=RGBColor(70,70,70)
def caption(txt):
 x=d.add_paragraph(); x.alignment=WD_ALIGN_PARAGRAPH.CENTER; x.paragraph_format.space_after=Pt(6); r=x.add_run(txt); r.bold=True

def table(headers, rows):
 t=d.add_table(rows=1, cols=len(headers)); t.alignment=WD_TABLE_ALIGNMENT.CENTER; t.style='Table Grid'
 for c,h in zip(t.rows[0].cells,headers): c.text=h
 for c in t.rows[0].cells:
  for r in c.paragraphs[0].runs: r.bold=True
 for row in rows:
  cells=t.add_row().cells
  for c,val in zip(cells,row): c.text=val
 return t

x=d.add_paragraph(style='Title'); x.alignment=WD_ALIGN_PARAGRAPH.CENTER; x.add_run('Chapter III')
x=d.add_paragraph(); x.alignment=WD_ALIGN_PARAGRAPH.CENTER; x.add_run('DESIGN AND METHODOLOGY').bold=True
p('This chapter presents the requirements analysis, system design, development methodology, testing plan, implementation plan, and evaluation framework for the Web-Based Project Management Hub for Optimized Extension Training Services at Isabela State University–Cauayan Campus.')

heading('Requirement Analysis',1)
heading('Data Flow Diagram',2)
p('The existing procedure begins when Program Leaders, Project Leaders, and Faculty Extensionists prepare program information, project proposals, supporting documents, accomplishment reports, and certificate requests. These records are submitted to the Extension Training Services Office through manual or separate electronic channels. ETS Personnel review the submissions, request revisions when necessary, file the records, and prepare status updates and institutional reports. Project information is stored in physical folders and separate electronic files, which makes retrieval, status monitoring, and report consolidation difficult.')
p('The proposed system will centralize these data flows. Authorized users will submit information through the web application. ETS Personnel will review and route proposals, update project status, manage documents, and generate reports and certificates. The system will send status notifications through the configured notification services and store records in a centralized database and document repository.')
caption('Figure 1. Existing Procedure Data Flow Diagram')
prompt('Create a clean academic Level 0 data flow diagram titled “Existing Procedure Data Flow Diagram of Extension Training Services at ISU–Cauayan.” Place the Extension Training Services Office at the center. Place Program Leaders, Project Leaders, Faculty Extensionists, and University Administration around it. Show program information, project proposals, project documents, accomplishment reports, certificate requests, status inquiries, and report requests as data flows. Use the central process “Manual Extension Project Management” and data stores Physical Records, Electronic Files, Proposal Records, Accomplishment Records, and Certificate Records. Use standard DFD notation, organized horizontal and vertical connectors, no crossing lines, readable labels, black and white academic styling, and landscape layout.')

heading('Fishbone Diagram',2)
p('The main problem identified in the existing process is delayed, scattered, and difficult-to-monitor extension project management. People-related causes include unclear responsibilities, manual follow-up, and limited communication. Process-related causes include manual submission, delayed review, unclear approval routing, and repeated revisions. Document-related causes include scattered files, duplicate records, missing attachments, and difficult retrieval. Technology-related causes include the absence of a centralized platform, limited automated notifications, and separate storage locations. Monitoring-related causes include difficult status tracking, delayed accomplishment updates, and weak project component monitoring. Reporting-related causes include manual consolidation, delayed reports, and time-consuming certificate preparation.')
caption('Figure 2. Fishbone Diagram of Problems in the Existing Procedure')
prompt('Create a professional academic fishbone diagram titled “Problems Encountered in the Existing Extension Project Management Process.” Place “Delayed, Scattered, and Difficult-to-Monitor Extension Project Management” at the fish head. Use six branches: People, Process, Documents, Technology, Monitoring, and Reporting. Add the causes described in the paragraph above. Use clear spacing, black and white lines, readable text, landscape layout, and no overlapping labels.')

heading('Timeline of the Project',1)
heading('Gantt Chart',2)
p('The project timeline covers requirements analysis, data gathering, system design, database design, interface design, module development, integration testing, user acceptance testing, ISO/IEC 25010 evaluation, revision, deployment, training, and maintenance. The schedule may be adjusted according to the approved capstone calendar and the availability of ETS personnel and respondents.')
caption('Figure 3. Gantt Chart of the Capstone Project')
prompt('Create a clean academic Gantt chart titled “Gantt Chart of the Capstone Project.” Use the activities Requirements Analysis, Data Gathering, System Design, Database Design, Interface Design, Module Development, Integration Testing, User Acceptance Testing, ISO/IEC 25010 Evaluation, System Revision, Deployment, User Training, and Maintenance. Use a monthly timeline with clearly shaded activity bars, a legend, readable labels, and landscape orientation.')
p('The activities are conducted sequentially, with selected activities overlapping to support efficient development. Requirements analysis and data gathering provide the basis for the system design. Development follows the approved design and database structure. Testing and user evaluation provide evidence for system revision before deployment, training, and maintenance.')

heading('Requirements Documentation',1)
heading('Technical Background',2)
p('The proposed system is a browser-based project management hub that centralizes extension program and project records. It uses role-based access to control the functions available to each authorized user. The application manages proposals, supporting documents, project components, accomplishment reports, notifications, certificates, and institutional reports. It is designed to support desktop and mobile browser access and may connect to email and SMS services for status notifications.')
heading('Materials',2)
heading('1. Software',3)
table(['Software','Purpose'],[
('Web browser','Provides access to the presentation layer and system screens.'),('Web server and PHP runtime','Processes requests and delivers the web application.'),('Relational database management system','Stores users, roles, projects, proposals, documents, reports, and activity records.'),('HTML, CSS, and JavaScript','Builds the user interface and browser interactions.'),('Email service and SMS gateway','Delivers system-generated notifications when configured.'),('Development and diagramming tools','Used for coding, database design, testing, documentation, and figure preparation.')])
p('The final manuscript should replace generic software descriptions with the exact software names and versions used during development and deployment.')
heading('2. Hardware',3)
table(['Hardware','Minimum specification or purpose'],[
('Development computer','Dual-core processor, 4 GB RAM, at least 20 GB available storage, and a modern web browser.'),('Deployment server or hosting account','PHP-compatible web server, relational database support, file storage, and SSL support.'),('Client device','Desktop, laptop, tablet, or mobile device with an updated browser and internet or local network access.'),('Network equipment','Router or internet connection sufficient for browser access and notification services.')])
p('The exact specifications should be updated based on the equipment used by the researchers and the host institution.')
heading('3. Data',3)
p('The data will come from the Extension Training Services Office of Isabela State University–Cauayan Campus and from authorized system users. The data includes user accounts, roles, program records, project records, project components, proposals, proposal reviews, supporting documents, accomplishment reports, certificates, notifications, and institutional reports. The year of acquisition should be stated according to the actual records and test data used in the study. Personal and confidential information must be collected, stored, and accessed only for the approved purpose of the study.')

heading('Use Case Diagram',1)
p('The use case diagram identifies the authorized users and the functions they perform. The System Administrator manages accounts, roles, permissions, and activity logs. ETS Personnel manages programs, projects, project components, proposals, documents, accomplishment reports, certificates, notifications, and reports. Program Leaders oversee programs and review project information. Project Leaders submit proposals, documents, and accomplishment reports. Faculty Extensionists submit supporting documents and accomplishment information.')
caption('Figure 4. Use Case Diagram of the Web-Based Project Management Hub')
prompt('Create a clean UML use case diagram titled “Use Case Diagram of the Web-Based Project Management Hub.” Place the System Administrator and ETS Personnel on the left, and Program Leader, Project Leader, and Faculty Extensionist on the right. Inside the system boundary, group the use cases into User and Access Management, Program and Project Management, Proposal Management, Document and Accomplishment Management, and Notification and Reporting. Connect actors only to the functions they perform. Use minimal crossing lines, standard UML notation, black and white academic style, and landscape orientation.')

heading('Functional Decomposition Diagram',1)
p('The functional decomposition diagram divides the system into modules derived from the revised objectives. The modules cover user and role management, programs, projects, project components, proposals, documents, accomplishment reports, notifications, and reports and certificates. System evaluation is treated as a research evaluation activity and is discussed in the ISO/IEC 25010 Evaluation Framework.')
caption('Figure 5. Functional Decomposition Diagram of the Web-Based Project Management Hub')
prompt('Create a clean hierarchical functional decomposition diagram titled “Functional Decomposition Diagram of the Web-Based Project Management Hub.” Place the system name at the top. Add nine modules: User and Role Management, Program Management, Project Management, Project Component Management, Proposal Management, Document Management, Accomplishment Report Management, Notification Management, and Reports and Certificate Management. Add concise subfunctions under each module. Use aligned boxes, no crossing lines, readable text, black and white academic styling, and landscape orientation.')

heading('System Flowchart',1)
p('The system flowchart begins with user login and role verification. After a successful login, the system displays a role-based dashboard. Users can manage programs and projects, submit proposals, upload documents, monitor project status, submit accomplishment reports, generate certificates and reports, or manage users and roles according to their permissions. Proposals are reviewed by ETS Personnel. A proposal may be approved or returned for revision. The system sends a notification after a status change and records important activities in the audit log.')
caption('Figure 6. System Flowchart of the Web-Based Project Management Hub')
prompt('Create a clean academic system flowchart titled “System Flowchart of the Web-Based Project Management Hub.” Use rounded rectangles for Start and End, rectangles for processes, diamonds for decisions, and arrows for flow. Show: Start, Access System, Enter Username and Password, Validate Login, Display Login Error, Verify User Role and Permissions, Display Role-Based Dashboard, Select Module, Submit Proposal, Upload Required Documents, ETS Reviews Proposal, Approved?, Return for Revision, Notify Submitter, Resubmit Proposal, Update Proposal Status, Notify Concerned Users, Monitor Project and Components, Submit Accomplishment Report, Review and Verify Report, Generate Certificate or Report, Record Activity in Audit Log, and End. Use clear Yes and No labels and minimal crossing arrows.')

heading('Entity Relationship Diagram',1)
p('The entity relationship diagram presents the database structure of the proposed system. Users are connected to roles and permissions through UserRoles. Programs contain projects, while projects contain project components, proposals, documents, and accomplishment reports. ProposalReviews records proposal decisions. Notifications are associated with users and activities. Reports summarize project and accomplishment data, while AuditLogs provide accountability for important system actions.')
caption('Figure 7. Entity Relationship Diagram of the Web-Based Project Management Hub')
prompt('Create a clean crow’s foot entity relationship diagram titled “Entity Relationship Diagram of the Web-Based Project Management Hub.” Include Users, Roles, Permissions, UserRoles, Programs, Projects, ProjectComponents, Proposals, ProposalReviews, Documents, AccomplishmentReports, Certificates, Notifications, Reports, and AuditLogs. Show primary keys, foreign keys, and the relationships described in the paragraph above. Use organized spacing, readable field names, no overlapping connectors, and landscape orientation.')

heading('Design of Software and Systems',1)
heading('Software Development Methodology',2)
p('The study will use an iterative development approach. Requirements are gathered from the ETS Office and intended users. The requirements are translated into system designs, database structures, user interfaces, and functional modules. A working version is tested, evaluated by users, revised based on findings, and prepared for deployment. This approach allows the researchers to improve the system while keeping the implementation aligned with the approved objectives and scope.')

heading('Development and Testing',2)
p('Development includes the creation of the user interface, authentication, role-based access, program and project management, proposal routing, document management, accomplishment reporting, notifications, reporting, certificate generation, and audit logging. Testing will be conducted at the module and integrated system levels. Defects and user feedback will be recorded and addressed before deployment.')
heading('Data Gathering Procedure',2)
for x in ['Secure approval from the concerned office and obtain the required permissions for data gathering.','Conduct interviews or consultations with ETS Personnel and selected intended users.','Document the existing submission, review, approval, monitoring, reporting, and filing procedures.','Collect representative forms, reports, document categories, and workflow requirements.','Validate the requirements and proposed system functions with the concerned personnel.','Conduct user acceptance testing and collect evaluation responses after the working system is demonstrated.']: numbered(x)
heading('Data Analysis Plan',2)
p('The responses will be summarized using frequency, percentage, and weighted mean. The results will be interpreted using the approved rating scale of the study. The evaluation will cover Functional Suitability, Usability, Performance Efficiency, and Security. The final interpretation should use the equivalent weight mean and interpretation table approved by the research adviser or panel.')
p('For percentage: % = (f / n) × 100, where f is the frequency and n is the total number of respondents. For weighted mean: WM = Σ(fx) / N, where f is the frequency, x is the rating weight, and N is the total number of respondents.')

heading('Different Software Testing',1)
table(['Testing activity','Purpose'],[
('Unit testing','Checks individual functions such as login, proposal submission, document upload, and report generation.'),('Integration testing','Checks the interaction among modules, database records, file storage, and notifications.'),('System testing','Checks the complete workflow from login through reporting and audit logging.'),('Usability testing','Checks whether intended users can understand and operate the system.'),('Security testing','Checks authentication, authorization, session handling, and access to protected records.'),('Performance testing','Checks response time and system behavior under expected use.'),('User acceptance testing','Confirms whether the system satisfies the approved operational requirements.')])

heading('Description of the Prototype',1)
p('The prototype is a web-based project management hub with role-based dashboards. It provides authorized users with access to programs, projects, project components, proposals, documents, accomplishment reports, notifications, certificates, reports, and account functions based on their assigned permissions. The prototype is intended to replace scattered manual records with a centralized and traceable workflow.')

heading('System Architecture',1)
p('The system uses a three-tier architecture. The presentation layer provides browser access through desktop and mobile devices. The application layer processes authentication, role permissions, project management, proposal routing, document management, accomplishment reports, notifications, reporting, certificates, and audit logging. The data layer stores structured records, uploaded files, and activity logs. Email and SMS services are connected to the application layer for automated notifications.')
caption('Figure 8. System Architecture of the Web-Based Project Management Hub')
prompt('Create a clean three-tier system architecture diagram titled “System Architecture of the Web-Based Project Management Hub.” Show Presentation Layer with desktop, laptop, tablet, and mobile browsers; Application Layer with authentication, role-based access, project management, proposal routing, document management, accomplishment reports, notifications, reports and certificates, and audit logging; and Data Layer with a relational database, document file storage, and audit log storage. Add Email Service and SMS Gateway as external services. Label arrows User Requests and Responses, Records and Documents, and Notification Messages. Use landscape orientation and no crossing lines.')

heading('Implementation Plan',1)
table(['Phase','Activities','Responsible persons','Estimated duration'],[
('Preparation','Confirm requirements, users, data sources, and approvals.','Researchers and ETS Personnel','1 week'),('Design','Prepare diagrams, database design, interface design, and workflow specifications.','Researchers','2 weeks'),('Development','Build and integrate the approved modules.','Researchers and Developer','4 weeks'),('Testing','Conduct unit, integration, system, security, performance, and user acceptance testing.','Researchers and Selected Users','2 weeks'),('Evaluation and revision','Conduct ISO/IEC 25010 evaluation and apply approved revisions.','Researchers and Respondents','2 weeks'),('Deployment and training','Install the system, configure the database, and orient authorized users.','Researchers and Administrator','1 week'),('Maintenance','Monitor operation, correct defects, and provide support.','Administrator and Researchers','Continuous')])
p('The implementation plan will be adjusted to the official capstone schedule and the availability of the project stakeholders.')

heading('Implementation Results',1)
p('The implementation results should present the completed system modules, testing results, user evaluation results, deployment status, and identified limitations. The final version of this section should be completed after testing and evaluation. It should report actual findings rather than anticipated results.')

heading('ISO/IEC 25010 Evaluation Framework',1)
p('The system will be evaluated using Functional Suitability, Usability, Performance Efficiency, and Security. Functional Suitability determines whether the required functions are complete, correct, and appropriate. Usability determines whether users can learn and operate the system effectively. Performance Efficiency determines response time and resource use. Security determines whether user accounts, project records, documents, and system functions are protected through authentication and authorization.')
caption('Figure 9. ISO/IEC 25010 Evaluation Framework')
prompt('Create a clean academic evaluation framework diagram titled “ISO/IEC 25010 Evaluation Framework for the Web-Based Project Management Hub.” Place the system at the center. Surround it with Functional Suitability, Usability, Performance Efficiency, and Security. At the bottom show ETS Personnel, Program Leaders, Project Leaders, and Faculty Extensionists as respondents. Connect the criteria to Overall System Evaluation and Recommended Improvements. Use a balanced landscape layout, readable labels, and black and white academic styling.')

heading('Chapter III Figure and Formatting Checklist',1)
for x in ['Use the same user roles and module names in every diagram.','Keep all wide diagrams in landscape orientation.','Use proper DFD, UML, flowchart, ERD, and architecture notation.','Keep connectors organized and remove unnecessary crossing lines.','Give every figure a correct number, title, and explanation.','Ensure every figure supports one or more Chapter 1 objectives.','Use Functional Suitability, Usability, Performance Efficiency, and Security consistently.','Replace each prompt with the final generated figure before printing.','Remove all handwritten annotations and template instructions from the final manuscript.','Update the Gantt chart with the actual capstone dates and the implementation results after testing.']: bullet(x)

for sec in d.sections:
 f=sec.footer.paragraphs[0]; f.alignment=WD_ALIGN_PARAGRAPH.CENTER; f.add_run('Chapter III Revised Design and Methodology').font.size=Pt(9)
d.core_properties.title='Chapter III Revised Design and Methodology'; d.core_properties.author=''; d.save(out); print(out)
