# TODO

#### Overview
| Task | Priority | Difficulty | Target version | Progress |
|------|:---------:|:----------:|:---------------|:--------:|
|Write fallback controller for legacy routes|High|Easy|v0.1|100%|
|Automated testing<sup>[1](#fn1)</sup>|Medium|Hard|v0.2|1%|
|Migrate Commands|Medium|Misc|v0.2|0%|
|Migrate API|Low|Misc|v0.2|10%|
|Recreate ExClient in new codebase|High|Moderate|v0.1|30%
|DailyBonus command<sup>[2](#fn2)</sup> |Medium|Easy|v0.2|80%|
|Add support for workers|Low|Unknown|vX.X|0%|
|Redis support<sup>[3](#fn3)</sup>|Medium|Moderate|v0.3|0%|
|Replace Sphinx search with ElasticSearch<sup>[4](#fn4)</sup>|Medium|Moderate|vX.X|0%|

#### ExClient progress
| Feature | Scope | Target version | Code coverage | Progress | Note |
|---------|:-----:|:---------------|:-------------:|:--------:|:-----|
|Authentication<sup>[5](#fn5)</sup>|Global|v0.1|0%|50%|Login with username/password is lacking|
|Index|Gallery|v0.1|0%|100%|Works in both list and thumbnail view|
|Search|Gallery|v0.1|0%|80%-100%|Work AFAIK. Needs tests for all use cases|
|Get Gallery|Gallery|v0.1|0%|100%|Uses API|
|Download Gallery|Gallery|v0.1|0%|0%|Should get file and stream output|

---
Footnotes:

<a name="fn1">1</a>: Difficulty depends on legacy code that is hard to test/mock. Prioritizing code migration/refactor above testing legacy code.

<a name="fn2">2</a>: This is a feature which will benefit people who donated and are eligible for daily perks. This command will open up a random gallery. Intended to be used in a cronjob.

<a name="fn3">3</a>: Add redis caching for caching gallery download states for a fixed period of time. Update cache in commands to assure redis data remains up to date.

<a name="fn4">4</a>: Sphinx search **MUST** be replaced the moment database has been migrated.

<a name="fn5">5</a>: Client will have support of using either a username/password combination or the values from the cookie (ipb_* values) directory 
