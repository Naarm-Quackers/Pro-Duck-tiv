# Welcome
## Set Up Instructions:
To check that you are running the correct version of node
in Terminal run:
- `node -v`
<br>Should print "v24.15.0".<br>
- `npm -v` 
<br>Should print "11.12.1".<br>

To clone the Project in desired folder and download dependencies run these commands in the terminal:
-   `git clone https://github.com/Naarm-Quackers/Pro-Duck-tiv.git`
-   `cd Pro-Duck-tiv/proDucktiv-vue-app`
-   `npm install` 
<br>Loads the npm packages listed in the package.json and package-lock.json files.<br>

When trying to clone the repo, if it ask for *Username* please enter your github **username** and for the *Password* enter your **personal access token** for the repo. 

## How to set up Personal Access Tokens for Origination
If you don’t have a personal access token for this organisations repo one can be created by:
- Clicking on the user profile icon in top right corner of github.com
- Click on Settings -> Developer Settings
- Click on Personal access tokens -> Fine-Grained tokens 
- Then Click Generate New Token 
- Make sure that the Resource Owner is **Naarm-Quackers**
- Select the Pro-Duck-tiv repo
- Then add Read and Write Repository permissions for actions, contents, issues, pull requests, and workflows (meta data will be added automatically) plus any extra permissions you think you would need.

## Current Tasks
### Adam:
- [ ] User Mood Table
- [ ] Welcome
- [ ] Welcome Unauthenticated View (Public landing page)
- [ ] WelcomeAuthenticatedView (Dashboard)
- [ ] Task Editor

### Amy:
- [ ] User Task Table
- [ ] Task
- [ ] Task Creation

### Eilia:
- [ ] About
- [ ] Calendar
- [ ] Menu / Navbar

### Dylan:
- [ ] User Information Database
- [ ] Registration
- [ ] Sign Up

### Justin:
- [ ] User Journal Table
- [ ] Journal
- [ ] Account

## Assigned Roles

### Adam: 
#### Contribution:

### Amy:
#### Contribution:

### Eilia:
#### Contribution:

### Dylan:
#### Contribution:

### Justin:
#### Contribution:

## Design Diagrams
### Basic Design Diagram:
![Basic Design](/Images/BasicDesign.png)

## Database Tables Info
- "Table element name" - table element long name, type, [attributes]
### "pdt_users" Table:
- "user_id"             - User ID, number, [unique, automatically generated]
- "user_name"           - Username, text, [unique, min-3]
- "user_password"       - Password, password, [min-8, include capital letter, include special characters]
- "user_first_name"     - First name, text, [letters only]
- "user_last_name"      - Last name, text, [letters only]
- "user_date_of_birth"  - Date of birth, date, [not in the future, optional]

### 'pdt_moods' Table:
- "mood_id"            - Mood ID, number, [unique, automatically generated]
- "user_id"            - User ID, number, [foreign key referencing pdt_users(user_id), cascades on delete/update]
- "mood_date"          - Mood date, date, [one entry per user per day, unique per user]
- "mood_value"         - Mood value, enum('up', 'down'), [required]

### 'pdt_journals' Table:
- "journal_id"         - Journal ID, number, [unique, automatically generated]
- "user_id"            - User ID, number, [foreign key referencing pdt_users(user_id), cascades on delete/update]
- "journal_name"       - Journal name, text, [required, max-255]
- "journal_body"       - Journal body, long text, [optional]
- "journal_date"       - Journal date, date, [required]
- "journal_status"     - Journal status, text, [optional, max-10]
- "created_at"         - Created at, timestamp, [automatically set on creation]

### 'pdt_tasks' Table:
- "task_id"            - Task ID, number, [unique, automatically generated]
- "user_id"            - User ID, number, [foreign key referencing pdt_users(user_id), cascades on delete/update] 
- "task_title"         - Task title, text, [required, max-250]
- "task_type"          - Task type, text, [required, max-100]
- "task_date"          - Due date, date, [required]
- "task_time"          - Due time, time, [required]
- "task_location"      - Task location, text, [required, max-225]
- "task_description"   - Task description, text, [required]
- "task_completed"     - Task completion status, boolean, [default false (0)]
- "created_at"         - Created at, timestamp, [automatically set on creation]
- "updated_at"         - Updated at, timestamp, [updated when task is modified]


