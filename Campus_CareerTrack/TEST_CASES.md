# Manual Test Cases — Campus CareerTrack

| ID | Test | Steps | Expected result |
|---|---|---|---|
| TC01 | Initial load | Open `index.html` | Overview dashboard and sample data appear |
| TC02 | Navigate | Click Opportunities, Applications, Saved jobs, Profile | Correct view is shown and breadcrumb changes |
| TC03 | Search | Search `React` in opportunities | Only matching opportunities appear |
| TC04 | Filter | Select Internship + Remote | Only matching roles appear |
| TC05 | Reset filters | Click Reset filters | Search clears and all filters reset |
| TC06 | Save job | Click bookmark on a card | Saved state and saved count update |
| TC07 | Persist saved job | Refresh browser and open Saved jobs | Saved role remains |
| TC08 | Required fields | Submit blank application form | Submission is blocked and errors appear |
| TC09 | Email validation | Enter invalid email | Submission is blocked |
| TC10 | Motivation validation | Enter fewer than 20 characters | Submission is blocked |
| TC11 | Consent validation | Leave confirmation unchecked | Submission is blocked |
| TC12 | Valid application | Complete all valid fields and submit | Application is added and dashboard updates |
| TC13 | Duplicate prevention | Try applying to the same role again | Application is not duplicated |
| TC14 | Status tabs | Select In review or Interview | Only matching applications appear |
| TC15 | Profile validation | Enter invalid email and save | Profile is not saved |
| TC16 | Profile persistence | Save profile and refresh | Updated profile remains |
| TC17 | Responsive layout | Narrow browser width to mobile size | Sidebar can open, cards stack, forms remain usable |
| TC18 | Empty search | Search an impossible term | Empty-state message appears |
