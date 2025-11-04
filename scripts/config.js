const AppConfig = {
  // Detect if we're on GitHub Pages
  isGitHubPages: window.location.hostname.includes('github.io'),
  
  getBasePath() {
    if (this.isGitHubPages) {
      const pathParts = window.location.pathname.split('/').filter(p => p);
      return pathParts.length > 0 ? `/${pathParts[0]}/` : '/';
    }
    return '/';
  },
  
  // Get full path with base
  getPath(path) {
    const cleanPath = path.startsWith('/') ? path.substring(1) : path;
    const basePath = this.getBasePath();
    
    return basePath.endsWith('/') 
      ? basePath + cleanPath 
      : basePath + '/' + cleanPath;
  },
  
  // Navigate to a path
  navigate(path) {
    window.location.href = this.getPath(path);
  }
};

window.AppConfig = AppConfig;
