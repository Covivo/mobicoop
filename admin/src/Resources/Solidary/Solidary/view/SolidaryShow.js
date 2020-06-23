import React from 'react';
import { makeStyles } from '@material-ui/core/styles';
import { useShowController } from 'react-admin';
import { Card, AppBar, Tabs, Tab } from '@material-ui/core';
import SolidaryShowInformation from './SolidaryShowInformation';
import SolidaryShowDetail from './SolidaryShowDetail';
import { SolidaryShowDiaries } from './SolidaryShowDiaries';

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
  const [tabActif, setTabActif] = React.useState(0);
  const { record } = useShowController(props);

  return record ? (
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
          <Tab label="Journal de suivi" />
        </Tabs>
      </AppBar>
      {tabActif === 0 && <SolidaryShowInformation record={record} />}
      {tabActif === 1 && <SolidaryShowDetail record={record} />}
      {tabActif === 2 && <SolidaryShowDiaries record={record} />}
    </Card>
  ) : null;
};

export default SolidaryShow;
