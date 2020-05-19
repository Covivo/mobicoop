import React from 'react';
import { AppBar as RAAppBar } from 'react-admin';
import Typography from '@material-ui/core/Typography';
import { makeStyles } from '@material-ui/core/styles';

const useStyles = makeStyles({
  title: {
    flex: 1,
    textOverflow: 'ellipsis',
    whiteSpace: 'nowrap',
    overflow: 'hidden',
  },
  spacer: {
    flex: 1,
  },
  logo: {
    height: 50,
  },
});

const AppBar = (props) => {
  const logo = process.env.REACT_APP_THEME_URL_LOGO;
  const classes = useStyles();

  return (
    <RAAppBar {...props}>
      <Typography variant="h6" color="inherit" className={classes.title} id="react-admin-title" />
      <img src={logo} alt="Logo" className={classes.logo} />
      <span className={classes.spacer} />
    </RAAppBar>
  );
};

export default AppBar;
