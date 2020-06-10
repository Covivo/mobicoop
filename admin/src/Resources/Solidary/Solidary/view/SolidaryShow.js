import React from 'react';
import { makeStyles } from '@material-ui/core/styles';
import { useShowController } from 'react-admin';
import { Card, AppBar, Tabs, Tab } from '@material-ui/core';
import SolidaryShowInformation from './SolidaryShowInformation';
import SolidaryAsks from './SolidaryAsks';

const useStyles = makeStyles((theme) => ({
  main_panel: {
    backgroundColor: 'white',
    padding: theme.spacing(2, 4, 3),
    marginTop: '2rem',
  },
  tab: {
    marginBottom: '1rem',
  },
}));

const SolidaryShow = (props) => {
  const classes = useStyles();
  const {
    basePath, // deduced from the location, useful for action buttons
    defaultTitle, // the translated title based on the resource, e.g. 'Post #123'
    loaded, // boolean that is false until the record is available
    loading, // boolean that is true on mount, and false once the record was fetched
    record, // record fetched via dataProvider.getOne() based on the id from the location
    resource, // the resource name, deduced from the location. e.g. 'posts'
    version, // integer used by the refresh feature
  } = useShowController(props);
  const [tabActif, setTabActif] = React.useState(0);

  console.log('record:', record);
  if (!record) {
    return null;
  }

  return (
    <Card className={classes.main_panel}>
      <AppBar position="static" color="default" className={classes.tab}>
        <Tabs
          value={tabActif}
          onChange={(event, newValue) => setTabActif(newValue)}
          indicatorColor="primary"
          textColor="primary"
          variant="fullWidth"
        >
          <Tab label="Informations" />
          <Tab label="Détails" />
        </Tabs>
      </AppBar>

      {tabActif === 0 && <SolidaryShowInformation {...props} record={record} />}
      {tabActif === 1 && <SolidaryAsks {...props} record={record} />}
    </Card>
  );
};

export default SolidaryShow;
