import React, { useEffect, useState } from 'react';
import { AppBar as RAAppBar, UserMenu } from 'react-admin';
import { Typography, MenuItem, makeStyles } from '@material-ui/core';
import { getUser } from '../../auth/authProvider';
import { usernameRenderer } from '../../utils/renderers';

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

const CustomUserMenu = (props) => {
  const userId = global.localStorage.getItem('id');
  const [user, setUser] = useState(null);

  useEffect(() => {
    getUser(userId).then(setUser);
  }, [userId]);

  return (
    <UserMenu {...props}>
      {user && <MenuItem>{usernameRenderer({ record: user })}</MenuItem>}
    </UserMenu>
  );
};

const AppBar = (props) => {
  const logo = process.env.REACT_APP_THEME_URL_LOGO;
  const classes = useStyles();

  return (
    <RAAppBar {...props} userMenu={<CustomUserMenu />}>
      <Typography variant="h6" color="inherit" className={classes.title} id="react-admin-title" />
      <img src={logo} alt="Logo" className={classes.logo} />
      <span className={classes.spacer} />
    </RAAppBar>
  );
};

export default AppBar;
