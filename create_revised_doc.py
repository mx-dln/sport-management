from docx import Document
from docx.shared import Inches, Pt, RGBColor
from docx.enum.text import WD_ALIGN_PARAGRAPH
from docx.enum.section import WD_SECTION
from docx.enum.style import WD_STYLE_TYPE
from docx.oxml import OxmlElement
from docx.oxml.ns import qn
from docx.enum.table import WD_TABLE_ALIGNMENT, WD_CELL_VERTICAL_ALIGNMENT
from pathlib import Path

out = Path(r'C:\Users\micha\OneDrive\Documents\Sites\sports-management\Chapter_1_and_3_Revised_Content.docx')
doc = Document()
sec = doc.sections[0]
sec.top_margin = Inches(.75); sec.bottom_margin = Inches(.7); sec.left_margin = Inches(.85); sec.right_margin = Inches(.85)
styles = doc.styles
styles['Normal'].font.name='Aptos'; styles['Normal'].font.size=Pt(10.5)
styles['Normal']._element.rPr.rFonts.set(qn('w:eastAsia'), 'Aptos')
for name,size,color in [('Title',22,'17365D'),('Heading 1',16,'17365D'),('Heading 2',13,'1F4E79'),('Heading 3',11,'1F4E79')]:
    s=styles[name]; s.font.name='Aptos Display' if name=='Title' else 'Aptos'; s.font.size=Pt(size); s.font.bold=True; s.font.color.rgb=RGBColor.from_string(color)
    s._element.rPr.rFonts.set(qn('w:eastAsia'), s.font.name)
styles['Title'].paragraph_format.space_after=Pt(6)
for n in ['Heading 1','Heading 2','Heading 3']:
    styles[n].paragraph_format.space_before=Pt(12); styles[n].paragraph_format.space_after=Pt(5)

# helpers
def shade(cell, fill):
    tcPr=cell._tc.get_or_add_tcPr(); shd=OxmlElement('w:shd'); shd.set(qn('w:fill'),fill); tcPr.append(shd)
def set_cell_text(cell,text,bold=False,color=None):
    cell.text=''; p=cell.paragraphs[0]; p.paragraph_format.space_after=Pt(0); r=p.add_run(text); r.bold=bold
    if color: r.font.color.rgb=RGBColor.from_string(color)
    cell.vertical_alignment=WD_CELL_VERTICAL_ALIGNMENT.CENTER

def bullet(text, level=0):
    p=doc.add_paragraph(style='List Bullet' if level==0 else 'List Bullet 2'); p.paragraph_format.space_after=Pt(2); p.add_run(text); return p

def numbered(text):
    p=doc.add_paragraph(style='List Number'); p.paragraph_format.space_after=Pt(2); p.add_run(text); return p

def para(text, boldlead=None):
    p=doc.add_paragraph(); p.paragraph_format.space_after=Pt(6)
    if boldlead and text.startswith(boldlead):
        p.add_run(boldlead).bold=True; p.add_run(text[len(boldlead):])
    else: p.add_run(text)
    return p

def prompt(text):
    p=doc.add_paragraph(); p.paragraph_format.left_indent=Inches(.2); p.paragraph_format.right_indent=Inches(.2); p.paragraph_format.space_after=Pt(8)
    r=p.add_run(text); r.font.name='Consolas'; r.font.size=Pt(9); r.font.color.rgb=RGBColor.from_string('404040')
    return p

def caption(text):
    p=doc.add_paragraph(); p.alignment=WD_ALIGN_PARAGRAPH.CENTER; p.paragraph_format.space_after=Pt(8); r=p.add_run(text); r.bold=True; r.italic=True; r.font.size=Pt(10); return p

# title page
t=doc.add_paragraph(style='Title'); t.alignment=WD_ALIGN_PARAGRAPH.CENTER; t.add_run('Revised Chapter 1 and Chapter 3 Content')
p=doc.add_paragraph(); p.alignment=WD_ALIGN_PARAGRAPH.CENTER; p.add_run('Web Based Project Management Hub for Optimized Extension Training Services at ISU Cauayan').bold=True
p=doc.add_paragraph(); p.alignment=WD_ALIGN_PARAGRAPH.CENTER; p.add_run('Objectives, scope, figure prompts, and explanations')
doc.add_paragraph('')
para('This document provides the revised objectives for Chapter 1 and the aligned content for Chapter 3. The diagram prompts are prepared for clean academic figure generation and should be followed by figure explanations that match the final diagrams.')
doc.add_page_break()

# Chapter 1
doc.add_heading('Chapter 1 Revised Objectives and Scope',1)
doc.add_heading('General Objective',2)
para('The study aims to develop a web-based project management hub for optimized Extension Training Services at Isabela State University–Cauayan Campus. The system will support the management, monitoring, documentation, communication, and reporting of extension programs, projects, project components, proposals, accomplishment reports, certificates, and supporting documents.')
doc.add_heading('Specific Objectives',2)
for x in [
'Assess the existing procedures, information flow, and challenges encountered by the Extension Training Services Office in managing extension programs, projects, project components, proposals, accomplishment reports, certificates, and supporting documents.',
'Design and develop a centralized web-based project management hub that will allow authorized users to manage extension programs, projects, project components, proposals, documents, accomplishment reports, certificates, and institutional reports.',
'Implement role-based access and approval routing for the submission, review, approval, and revision of project proposals and related documents.',
'Provide automated notifications to inform authorized users about proposal submissions, approval decisions, returned revisions, project status updates, and other important activities.',
'Provide monitoring and reporting functions that will generate project status reports, accomplishment summaries, certificates, and institutional reports.',
'Evaluate the developed system based on selected ISO/IEC 25010 quality characteristics, specifically Functional Suitability, Usability, Performance Efficiency, and Security.',
'Determine the level of user acceptance of the system among ETS Personnel, Program Leaders, Project Leaders, and Faculty Extensionists.'
]: numbered(x)
doc.add_heading('Scope and Delimitation',2)
para('The study covers the design and development of a centralized web-based project management hub for the Extension Training Services Office of Isabela State University–Cauayan Campus. The system will manage extension programs, projects, project components, proposals, supporting documents, accomplishment reports, certificates, notifications, and institutional reports.')
para('The system will provide role-based access for the System Administrator, ETS Personnel, Program Leaders, Project Leaders, and Faculty Extensionists. It will support proposal submission, document uploading, proposal review, approval routing, revision requests, project monitoring, accomplishment submission, notification delivery, certificate generation, report generation, and activity recording.')
para('The system will be evaluated using Functional Suitability, Usability, Performance Efficiency, and Security. The study is limited to extension training programs and projects handled by the ETS Office of ISU–Cauayan Campus. It will not cover unrelated university offices, financial accounting, procurement, payroll, or full institutional automation. Notifications will be limited to system-generated email, SMS, or in-system alerts related to project activities and status changes.')

# Chapter 3
doc.add_page_break(); doc.add_heading('Chapter 3 Revised System Analysis and Design',1)
para('This chapter presents the analysis and design of the proposed Web-Based Project Management Hub. The figures are aligned with the revised objectives in Chapter 1 and describe the existing process, proposed system functions, data structure, architecture, implementation flow, and evaluation framework.')

sections=[
('3.1 Existing Procedure Data Flow Diagram','Existing Procedure Data Flow Diagram of Extension Training Services at ISU–Cauayan',
'''Create a clean academic Level 0 data flow diagram titled “Existing Procedure Data Flow Diagram of Extension Training Services at ISU–Cauayan.” Use standard DFD notation. Place the Extension Training Services Office at the center. Place Program Leaders, Project Leaders, Faculty Extensionists, and University Administration around the system. Show external data flows such as program information, project proposals, project documents, accomplishment reports, certificate requests, project status inquiries, and institutional report requests. Show the central process as “Manual Extension Project Management.” Show separate data stores labeled Physical Records, Electronic Files, Proposal Records, Accomplishment Records, and Certificate Records. Use organized horizontal and vertical connectors with no crossing lines. Use black and white academic styling, landscape layout, readable labels, and no decorative elements.''',
'''The existing procedure data flow diagram presents the current manual process used by the Extension Training Services Office. Program Leaders, Project Leaders, and Faculty Extensionists submit program information, project proposals, supporting documents, accomplishment reports, and certificate requests to the ETS Office. ETS Personnel manually review, organize, store, and retrieve these records using physical files and separate electronic folders. Project status updates, reports, and documents are manually communicated to concerned users. This process may result in delayed approvals, repeated follow-ups, scattered records, difficulty tracking project components, and time-consuming report preparation. The diagram establishes the need for a centralized web-based project management hub.'''),
('3.2 Fishbone Diagram','Problems Encountered in the Existing Extension Project Management Process',
'''Create a professional academic fishbone diagram titled “Problems Encountered in the Existing Extension Project Management Process.” Place the main problem at the right side: “Delayed, Scattered, and Difficult-to-Monitor Extension Project Management.” Create six major branches labeled People, Process, Documents, Technology, Monitoring, and Reporting. Under People, include unclear responsibilities, manual follow-up, and limited communication. Under Process, include manual submission, delayed review, unclear approval routing, and repeated revisions. Under Documents, include scattered files, duplicate records, missing attachments, and difficult retrieval. Under Technology, include no centralized platform, limited automated notifications, and separate storage locations. Under Monitoring, include difficult project status tracking, delayed accomplishment updates, and weak component monitoring. Under Reporting, include manual consolidation, delayed reports, and time-consuming certificate preparation. Use clean black and white lines, clear spacing, landscape layout, and no overlapping text.''',
'''The fishbone diagram identifies the major causes of problems in the existing extension project management process. These causes are grouped into People, Process, Documents, Technology, Monitoring, and Reporting. The current process depends heavily on manual coordination, separate storage locations, and personal follow-up. These conditions affect proposal routing, document retrieval, project monitoring, accomplishment reporting, certificate preparation, and institutional report generation. The diagram supports the development of a centralized system with role-based access, automated notifications, organized document storage, and monitoring tools.'''),
('3.3 Use Case Diagram','Use Case Diagram of the Web-Based Project Management Hub',
'''Create a clean UML use case diagram titled “Use Case Diagram of the Web-Based Project Management Hub.” Place a large system boundary in the center labeled “Web-Based Project Management Hub.” Place the System Administrator and ETS Personnel on the left side. Place the Program Leader, Project Leader, and Faculty Extensionist on the right side. Use separate groups inside the system boundary: User and Access Management with Manage User Accounts, Assign Roles, Manage Permissions, and View Activity Logs; Program and Project Management with Manage Programs, Manage Projects, Manage Project Components, and Monitor Project Status; Proposal Management with Submit Proposal, Review Proposal, Approve Proposal, Return Proposal for Revision, and View Proposal Status; Document and Accomplishment Management with Upload Documents, Review Documents, Submit Accomplishment Report, and Verify Accomplishment Report; Notification and Reporting with Receive Notifications, Generate Reports, Generate Certificates, and View Project Summaries. Connect each actor only to the functions they perform. Use organized actor placement, minimal crossing lines, standard UML notation, black and white academic style, and landscape orientation.''',
'''The use case diagram presents the interaction between authorized users and the web-based project management hub. The System Administrator manages user accounts, roles, permissions, and activity records. ETS Personnel manages programs, projects, project components, proposals, documents, accomplishment reports, certificates, notifications, and institutional reports. Program Leaders oversee programs and review project information. Project Leaders submit project proposals, supporting documents, and accomplishment reports. Faculty Extensionists provide project-related documents and accomplishment information. The diagram reflects the role-based access and approval routing requirements stated in the objectives.'''),
('3.4 Functional Decomposition Diagram','Functional Decomposition Diagram of the Web-Based Project Management Hub',
'''Create a clean functional decomposition diagram titled “Functional Decomposition Diagram of the Web-Based Project Management Hub.” Place the main system at the top: “Web-Based Project Management Hub.” Divide it into nine major modules: User and Role Management; Program Management; Project Management; Project Component Management; Proposal Management; Document Management; Accomplishment Report Management; Notification Management; Reports and Certificate Management. Add subfunctions for each module: manage users, assign roles, manage permissions, record activities; create, update, and view programs; create, update, monitor status, and view project details; add components, assign responsible persons, and track progress; submit, review, approve, return for revision, and track proposal status; upload, categorize, review, and download documents; submit, review, verify, and track accomplishments; proposal, approval, revision, and status notifications; project reports, accomplishment summaries, certificates, and institutional reports. Use a hierarchical layout, aligned boxes, no crossing lines, readable text, black and white academic styling, and landscape orientation.''',
'''The functional decomposition diagram divides the proposed system into major modules and subfunctions. The modules are based on the revised objectives and scope of the study. The system provides functions for managing users, programs, projects, project components, proposals, documents, accomplishment reports, notifications, reports, and certificates. System evaluation is not included as a functional system module because it is part of the research evaluation process.'''),
('3.5 System Flowchart','System Flowchart of the Web-Based Project Management Hub',
'''Create a clean academic system flowchart titled “System Flowchart of the Web-Based Project Management Hub.” Use standard flowchart symbols: rounded rectangles for Start and End, rectangles for processes, diamonds for decisions, and arrows for flow direction. Arrange the flow vertically with organized side branches. Flow: Start → Access System → Enter Username and Password → Validate Login. If invalid: Display Login Error → Return to Login. If valid: Verify User Role and Permissions → Display Role-Based Dashboard → Select Module. From the dashboard show Manage Programs and Projects, Submit Proposal, Upload Documents, Monitor Project Status, Submit Accomplishment Report, Generate Certificate, Generate Report, and Manage Users and Roles. For proposal processing: Submit Proposal → Upload Required Documents → ETS Reviews Proposal → Approved? If no: Return Proposal for Revision → Notify Submitter → Resubmit Proposal. If yes: Update Proposal Status → Notify Concerned Users → Monitor Project and Components. Continue to Submit Accomplishment Report → Review and Verify Report → Generate Certificate or Report → Record Activity in Audit Log → End. Use clear Yes and No labels, minimal crossing arrows, consistent spacing, black and white academic design, and landscape orientation.''',
'''The system flowchart presents the sequence of activities performed in the proposed system. Users first log in, after which the system validates their credentials and verifies their role and permissions. The system then displays a dashboard based on the user’s authorized functions. Users may manage programs and projects, submit proposals, upload documents, monitor project status, submit accomplishment reports, and generate reports or certificates. Proposals are reviewed by ETS Personnel. Approved proposals proceed to project monitoring, while rejected or incomplete proposals are returned for revision. The system sends notifications whenever proposal or project statuses change and records important activities in the audit log.'''),
('3.6 Entity Relationship Diagram','Entity Relationship Diagram of the Web-Based Project Management Hub',
'''Create a clean crow’s foot entity relationship diagram titled “Entity Relationship Diagram of the Web-Based Project Management Hub.” Include these entities: Users, Roles, Permissions, UserRoles, Programs, Projects, ProjectComponents, Proposals, ProposalReviews, Documents, AccomplishmentReports, Certificates, Notifications, Reports, and AuditLogs. Show these relationships: Users may have many Roles through UserRoles; Roles may have many Permissions; a Program may contain many Projects; a Project may contain many ProjectComponents; a Project may have many Proposals; a Proposal may have many ProposalReviews and Documents; a Project may have many AccomplishmentReports; an AccomplishmentReport may produce a Certificate; Users may receive many Notifications; Users may create many AuditLogs; Reports may summarize Programs, Projects, Documents, and AccomplishmentReports. Show primary keys and foreign keys. Use clear entity boxes, readable field names, organized spacing, no overlapping connectors, and landscape orientation.''',
'''The entity relationship diagram shows the logical structure of the system database. Users are connected to roles and permissions through the UserRoles entity. Programs contain projects, while projects contain project components, proposals, documents, and accomplishment reports. ProposalReviews records the actions and decisions made during the approval process. Notifications are associated with users and project activities. Reports summarize project information and accomplishments, while AuditLogs record important system activities. The relationships support role-based access, proposal routing, document management, monitoring, reporting, and accountability.'''),
('3.7 System Architecture','System Architecture of the Web-Based Project Management Hub',
'''Create a clean three-tier system architecture diagram titled “System Architecture of the Web-Based Project Management Hub.” Use three horizontal layers. Presentation Layer: Desktop Browser, Laptop Browser, Tablet Browser, Mobile Browser. Application Layer: Login and Authentication, Role-Based Access Control, Program and Project Management, Proposal Routing, Document Management, Accomplishment Reports, Notifications, Reports and Certificates, Audit Logging. Data Layer: Centralized Relational Database, Document File Storage, Audit Log Storage. On the right side show external services: Email Service and SMS Gateway. Connect the presentation layer to the application layer with arrows labeled “User Requests and Responses.” Connect the application layer to the data layer with arrows labeled “Records, Documents, Statuses, and Reports.” Connect the application layer to external services with arrows labeled “Notification Messages.” Use clear labels, landscape orientation, and no crossing lines.''',
'''The system architecture follows a three-tier structure. The presentation layer allows authorized users to access the system through desktop, laptop, tablet, or mobile browsers. The application layer contains authentication, role-based access, program and project management, proposal routing, document management, accomplishment reporting, notifications, reporting, certificate generation, and audit logging. The data layer stores user information, project records, proposal records, documents, accomplishment reports, certificates, notifications, reports, and activity logs. The application layer also connects to email and SMS services to send automated notifications to authorized users.'''),
('3.8 System Implementation and Deployment Flow','System Implementation and Deployment Flow',
'''Create a clean academic process flow diagram titled “System Implementation and Deployment Flow.” Show the following sequential stages: Requirements Analysis → Process and Data Analysis → System Design → Database Design → Interface Design → Module Development → Integration Testing → User Acceptance Testing → ISO/IEC 25010 Evaluation → System Revision → Deployment → User Training → Maintenance and Support. Add feedback arrows from User Acceptance Testing, ISO/IEC 25010 Evaluation, and Maintenance and Support back to System Revision. Use rounded rectangles for activities, arrows for sequence, a clean horizontal layout, readable labels, black and white academic styling, and landscape orientation.''',
'''The implementation and deployment flow presents the development activities for the proposed system. The process begins with requirements and data analysis, followed by system design, database design, interface design, development, and testing. User acceptance testing and ISO/IEC 25010 evaluation are conducted before deployment. The results are used to revise and improve the system. After deployment, users receive training and the system undergoes continuous maintenance and support.'''),
('3.9 ISO/IEC 25010 Evaluation Framework','ISO/IEC 25010 Evaluation Framework for the Web-Based Project Management Hub',
'''Create a clean academic evaluation framework diagram titled “ISO/IEC 25010 Evaluation Framework for the Web-Based Project Management Hub.” Place the system at the center: “Web-Based Project Management Hub.” Around it, place four evaluation criteria: Functional Suitability with completeness, correctness, and appropriateness of system functions; Usability with ease of learning, ease of operation, user interface clarity, and user satisfaction; Performance Efficiency with response time, resource usage, and system performance; Security with authentication, authorization, confidentiality, integrity, and protection of user data. At the bottom show respondents: ETS Personnel, Program Leaders, Project Leaders, and Faculty Extensionists. Use arrows from respondents to the four evaluation criteria and arrows from the criteria to “Overall System Evaluation and Recommended Improvements.” Use a clean balanced layout, readable labels, black and white academic styling, and landscape orientation.''',
'''The ISO/IEC 25010 evaluation framework evaluates the developed system using four quality characteristics: Functional Suitability, Usability, Performance Efficiency, and Security. Functional Suitability measures whether the system provides the required functions correctly and completely. Usability measures whether users can learn and operate the system easily. Performance Efficiency measures the system’s response time and resource usage. Security measures the protection of accounts, records, documents, and other system information. The respondents include ETS Personnel, Program Leaders, Project Leaders, and Faculty Extensionists. Their evaluation results will be used to identify the strengths and areas for improvement of the developed system.''')]

for heading,title,pr,ex in sections:
    doc.add_heading(heading,1); doc.add_heading(title,2)
    p=doc.add_paragraph(); r=p.add_run('Diagram generation prompt'); r.bold=True; r.font.color.rgb=RGBColor.from_string('1F4E79')
    prompt(pr)
    p=doc.add_paragraph(); r=p.add_run('Figure explanation'); r.bold=True; r.font.color.rgb=RGBColor.from_string('1F4E79')
    para(ex)

# checklist
doc.add_page_break(); doc.add_heading('Final Consistency Checklist',1)
for x in [
'Use “project management hub” consistently throughout the chapter.',
'Use “project component” consistently throughout the chapter.',
'Use “Faculty Extensionists” consistently.',
'Use “ETS Personnel” consistently.',
'Use “Proposal Review” and “Approval Routing” consistently.',
'Use “Performance Efficiency” instead of “Efficiency” or “Usability Performance.”',
'Keep System Evaluation in Section 3.9 and do not present it as a system module.',
'Use landscape orientation for wide diagrams.',
'Use the same actor names in the use case diagram and explanations.',
'Use the same module names in the functional decomposition diagram and flowchart.',
'Give every figure a section number, title, and explanation.',
'Remove handwritten annotations from the final figures.',
'Confirm that every diagram directly supports one or more revised objectives.'
]: bullet(x)

# footer
for section in doc.sections:
    footer=section.footer.paragraphs[0]; footer.alignment=WD_ALIGN_PARAGRAPH.CENTER
    footer.add_run('Revised Chapter 1 and Chapter 3 Content').font.size=Pt(8)

doc.core_properties.title='Revised Chapter 1 and Chapter 3 Content'
doc.core_properties.subject='Web Based Project Management Hub for Optimized Extension Training Services'
doc.core_properties.author=''
doc.save(out)
print(out)
