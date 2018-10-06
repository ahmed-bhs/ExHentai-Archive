# TODO

#### Overview
| Task | Priority | Difficulty | Target version | Progress |
|------|:---------:|:----------:|:---------------|:--------:|
|Write fallback controller for legacy routes|High|Easy|v0.1|100%|
|Automated testing<sup>[1](#fn1)</sup>|Medium|Hard|v0.2|10%|
|Migrate Commands|Medium|Misc|v0.2|10%|
|Migrate API|Low|Misc|v0.2|10%|
|Recreate ExClient in new codebase|High|Moderate|v0.1|50%|
|DailyBonus command<sup>[2](#fn2)</sup> |Medium|Easy|v0.2|100%|
|Add support for workers<sup>[3](#fn3)</sup>|Medium|Unknown|vX.X|0%|
|Redis support<sup>[4](#fn4)</sup>|Medium|Moderate|v0.3|0%|
|Replace Sphinx search with ElasticSearch<sup>[5](#fn5)</sup>|Medium|Moderate|vX.X|30%|

#### ExClient progress
| Feature | Scope | Target version | Code coverage | Progress | Note |
|---------|:-----:|:---------------|:-------------:|:--------:|:-----|
|Authentication<sup>[6](#fn6)</sup>|Global|v0.1|80%|50%|Login with username/password is lacking|
|Index|Gallery|v0.1|100%|100%|Works in both list and thumbnail view|
|Search|Gallery|v0.1|80%|80%-100%|Needs extra logic for (multiple tags)|
|Get Gallery|Gallery|v0.1|100%|100%|Uses API|
|Download Gallery|Gallery|v0.1|33%|33%|ZIP works. Should get file and stream output|

#### Migrations
| Component | Code coverage | Progress | Comment |
|-----------|:-------------:|:--------:|---------|
|Command: Add Gallery|0%|0%||
|Command: Archive|0%|0%||
|Command: Audit|0%|0%||
|Command: Cleanup|0%|0%||
|Command: EditGallery|0%|0%||
|Command: ForceAudit|0%|0%|To be merged with Audit command as -f option|
|Command: Thumbnails|0%|0%||
|API: Galleries|0%|0%||
|API: Gallery|0%|0%|Merge with Galleries|
|API: ArchiveImage|0%|0%||
|API: GalleryThumbnail|0%|0%||
|API: AddGallery|0%|0%|Merge with AddGalleries|
|API: AddGalleries|0%|0%||
|API: HasGallery|0%|0%|Merge with HasGalleries|
|API: HasGalleries|0%|0%||
|API: DeleteGallery|0%|0%||
|API: Download|0%|0%||
|API: Suggest|0%|0%||
|API: Flush|0%|0%||
|API: Indexer|0%|0%||
|API: Test|0%|0%|Replace with healthcheck endpoint for service report|
|API: Update|0%|0%||
|API: UpdateColor|0%|0%|wot?|

---
Footnotes:

<a name="fn1">1</a>: Difficulty depends on legacy code that is hard to test/mock. Prioritizing code migration/refactor above testing legacy code.

<a name="fn2">2</a>: This is a feature which will benefit people who donated and are eligible for daily perks. This command will open up a random gallery. Intended to be used in a cronjob.

<a name="fn3">3</a>: Adding support for workers will allow spreading the load of heavy tasks across multiple workers/nodes. This could increase speeds of generating thumbnails, downloads (max 4 concurrent. EH restriction)

<a name="fn4">4</a>: Add redis caching for caching gallery download states for a fixed period of time. Update cache in commands to assure redis data remains up to date.

<a name="fn5">5</a>: Sphinx search **MUST** be replaced the moment database has been migrated.

<a name="fn6">6</a>: Client will have support of using either a username/password combination or the values from the cookie (ipb_* values) directory 
