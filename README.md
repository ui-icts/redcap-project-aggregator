## Project Aggregator

### Description
This module allows data from multiple source REDCap projects to be aggregated into a single destination project. Source projects are grouped by having an identical "Project Note" string and additional per project metadata (such as title and IRB number) can optionally be included in the aggregated data.

### Configuration
This module must be enabled at the project-level of the DESTINATION project in which the aggregate data will be stored. Once enabled, the project note to be searched for in source projects must be defined in the module configuration, as well as a selection of instruments/fields/metadata to be imported. The project note can be set via the Project Setup page (click "Modify project title, purpose, etc." button).

The destination project must include all the exactly named project fields being imported, as well as any additional fields for project metadata.

An API token can optionally be defined to erase all data in the destination project prior to each aggregate import. This is highly recommended as the module does no checking to see if data has changed, so in the event records are deleted in a source project, these changes may not be reflected in the destination project.

### Usage
The aggregation process will be automatically run once every 24 hours or can be manually run via a link in the project sidebar. The manual process will provide an "Import Status" for each individual project so they can be checked for errors (such as missing fields).

It is recommended that this module's use be restricted to super users only as it could allow access to REDCap data from more projects than the user has been granted explicit access to. This issue will be addressed in future updates.