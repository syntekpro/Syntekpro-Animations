# Releases

This folder stores versioned plugin zip packages for manual distribution.

Current package target:
- `syntekpro-animations-2.4.0.zip`

Generate package locally:

```powershell
./scripts/build-release.ps1 -Version 2.4.0
```

Automated package and release:
- Push a tag like `v2.4.0`
- GitHub Actions workflow `.github/workflows/release.yml` builds and attaches the zip to the GitHub Release
