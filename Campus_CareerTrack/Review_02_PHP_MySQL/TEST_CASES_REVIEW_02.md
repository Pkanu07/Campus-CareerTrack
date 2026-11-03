# Review 02 Test Cases

| No. | Test | Expected result |
|---|---|---|
| 1 | Register with valid student details | Account and profile created; dashboard opens |
| 2 | Register with invalid email or short password | Server rejects input |
| 3 | Register an already-used email | Duplicate account prevented |
| 4 | Log in with wrong password | Generic invalid credentials message |
| 5 | Open dashboard while logged out | Redirected to login |
| 6 | Apply with statement shorter than 20 characters | Application rejected |
| 7 | Apply to same opportunity twice | Duplicate application prevented |
| 8 | Submit valid application | Application stored with Pending status |
| 9 | Student opens admin URL | Access denied |
| 10 | Admin creates opportunity | Opportunity appears in listing |
| 11 | Admin hides opportunity | It no longer appears to students |
| 12 | Admin updates application status | New status saved and visible on student dashboard |
