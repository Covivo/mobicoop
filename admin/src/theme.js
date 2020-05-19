import { createMuiTheme } from '@material-ui/core/styles';

export default createMuiTheme({
  palette: {
    primary: {
      main: `#${process.env.REACT_APP_THEME_PRIMARY_COLOR}`,
    },
    secondary: {
      main: `#${process.env.REACT_APP_THEME_SECONDARY_COLOR}`,
    },
    error: {
      main: `#${process.env.REACT_APP_THEME_ERROR_COLOR}`,
    },
    contrastThreshold: process.env.REACT_APP_THEME_CONTRAST_THRESHOLD,
    tonalOffset: process.env.REACT_APP_THEME_TONAL_OFFSET,
    type: `${process.env.REACT_APP_THEME_TYPE}`,
    background: {
      paper: `#${process.env.REACT_APP_THEME_BACKGROUND_PAPER_COLOR}`,
      default: `#${process.env.REACT_APP_THEME_BACKGROUND_DEFAULT_COLOR}`,
    },
  },
});
