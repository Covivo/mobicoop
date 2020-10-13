import React, { useState } from 'react';
import { Card, CardContent, Paper, Tabs, Tab } from '@material-ui/core';
import { Title, useTranslate } from 'react-admin';

import SimplifiedDashboard from './SimplifiedDashboard';
import KibanaWidget from './KibanaWidget';

const Dashboard = () => {
  const [dashboard, setDashboard] = useState(0);
  return (
    <Card>
      <Title title="Dashboard" />
      <Paper style={{ marginBottom: '1rem' }}>
        <Tabs
          value={dashboard}
          onChange={(e, value) => setDashboard(value)}
          indicatorColor="primary"
          textColor="primary"
        >
          <Tab label="Simplifié" />
          <Tab label="Détaillé" />
        </Tabs>
      </Paper>

      <CardContent>{dashboard == 0 ? <SimplifiedDashboard /> : <KibanaWidget />}</CardContent>
    </Card>
  );
};

export default Dashboard;
