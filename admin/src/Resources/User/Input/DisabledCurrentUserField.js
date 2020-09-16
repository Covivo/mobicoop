import React, { useState, useEffect } from 'react';
import TextField from '@material-ui/core/TextField';
import { useDataProvider } from 'react-admin';

const DisabledCurrentUserField = (props) => {
  const dataProvider = useDataProvider();
  const [currentUserName, setCurrentUserName] = useState('...');

  useEffect(() => {
    global.localStorage.getItem('id');

    dataProvider
      .getOne('users', { id: global.localStorage.getItem('id') })
      .then(({ data }) => {
        setCurrentUserName(`${data.givenName} ${data.familyName}`);
      })
      .catch((e) => {
        console.error(e);
      });
  });

  return <TextField disabled value={currentUserName} label={props.label} />;
};

export default DisabledCurrentUserField;
