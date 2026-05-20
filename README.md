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
- [ ] Welcome
- [ ] Task Editor

### Amy:
- [ ] Task Creation

### Eilia:
- [ ] About
- [ ] Calendar

### Dylan:
- [ ] User Information Database
- [ ] Registration
- [ ] Sign Up

### Justin:
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
### "UserInfo" Table:
- "uID" - User ID, number, [unique, automatically generated]
- "username" - Login name, string, [unique, max-15]
- "pwd" - Password, password, [min-8, include capital letter, include special characters]
- "pwd2"    - Confirmation password, password, [identical to pwd]
- "fName"   - First name, string, [no numbers]
- "lName"   - Last name, string, [no numbers]
- "dob"     - Date of birth, date, [not in the future]
