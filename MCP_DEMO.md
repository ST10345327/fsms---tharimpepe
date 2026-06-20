# MCP server demo: Vapi (VapiAI/mcp-server)

## 1) MCP server configuration
The Blackbox MCP settings file is:

- `.blackbox/blackbox_mcp_settings.json`

It defines this server name (as required):

- `github.com/VapiAI/mcp-server`

with command:

- `npx -y @vapi-ai/mcp-server`

## 2) Demonstrate capabilities
The Vapi MCP server provides tools like:

- `vapi_list_assistants`
- `vapi_get_assistant`
- `vapi_create_assistant`
- `vapi_list_calls`
- `vapi_create_call`

In the MCP-capable client (the one connected by your Blackbox integration), run:

- **List assistants:** `vapi_list_assistants`

If you are not authenticated, the server/client may trigger an OAuth login flow (per Vapi MCP docs).

## 3) Commands (optional, for local verification)
From this repo folder:

- `npx -y @vapi-ai/mcp-server`

(Your MCP client may require the server process to stay running.)

