import React from 'react';
import { makeStyles } from '@material-ui/core/styles';
import AvatarField from './AvatarField';

const useStyles = makeStyles((theme) => ({
  root: {
    display: 'flex',
    flexWrap: 'nowrap',
    alignItems: 'center',
  },
  avatar: {
    marginRight: theme.spacing(1),
  },
}));

/* 
  Expected User record

  {
    @id: "/users/4"
    @type: "User"
    id: 4
    givenName: "Hervé"
    shortFamilyName: "F."
    avatars: ["/images/avatarsDefault/square_100.svg", "/images/avatarsDefault/square_250.svg"]
  }
*/

const FullNameField = ({ record, source, size }) => {
  const classes = useStyles();
  const user = source && record[source] ? record[source] : record;

  return user ? (
    <div className={classes.root}>
      <AvatarField className={classes.avatar} record={user} size={size} />
      {user.givenName} {user.familyName || user.shortFamilyName}
    </div>
  ) : null;
};

export default FullNameField;
