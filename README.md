# ClickUp for FreeScout

Create ClickUp tasks from a FreeScout conversation, or link an existing ClickUp task to one. A sidebar on each conversation shows the linked tasks and lets you unlink them.

Linking is stateless: nothing about a task is stored in FreeScout. Everything shown in the sidebar is fetched live from the ClickUp API using a custom field on the task that stores the conversation's FreeScout ID.

## Setup

1. Copy this module into your FreeScout `Modules` directory as `ClickupIntegration`.
2. In FreeScout, go to **Manage** -> **Modules** and activate it.
3. Go to **Manage** -> **Settings** -> **ClickUp** to configure it.

## Settings

**General**
- **ClickUp API Token**: generated in ClickUp under **Settings** -> **Apps**.
- **Integration Enabled**: leave off until every field below is set.
- **Environment**: prefixes the FreeScout ID written to a task (`fs-dev-1`, `fs-prod-1`, ...). Edit the list of environments in `Config/config.php`.

**Linking Configuration**
- **Team Id**: used to search for tasks already linked to a conversation.
- **Space Id**: used to fetch tags for the task form.
- **List Id**: the list new tasks are created in.

**Custom Fields Configuration**

Create these 4 custom fields on the target ClickUp list, then paste each field's Id (from the [ClickUp GetAccessibleCustomFields endpoint](https://clickup.com/api/clickupreference/operation/GetAccessibleCustomFields/)) into the matching setting:

- **FreeScout Id** (text): unique identifier for the conversation, used to find its linked tasks.
- **FreeScout URL** (url): link back to the conversation.
- **Submitter Name** (text): name of the person who submitted the conversation.
- **Submitter Email** (email): email of the person who submitted the conversation.

## Limitations

- New tasks can only be created in one list.
- ClickUp's API does not support searching tasks by title or description, so linking an existing task requires its URL or task ID.
- Assignees on a new task can only be people, not ClickUp teams.

## Credits

Fork of [ztersinc/freescout-clickup-module](https://github.com/ztersinc/freescout-clickup-module), licensed AGPL-3.0.

## Updates

FreeScout checks `module.json` on GitHub and offers new releases on the Modules page. Each release ships a `ClickupIntegration.zip` whose top folder is `ClickupIntegration`, so it also installs by unzipping straight into `Modules`.

This module uses the same alias as the original ztersinc module (`clickupintegration`) and is a drop-in replacement for it. The two cannot be installed side by side.
