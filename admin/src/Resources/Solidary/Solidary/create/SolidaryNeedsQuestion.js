import React from 'react';
import { CheckboxGroupInput, useGetList } from 'react-admin';
import { LinearProgress } from '@material-ui/core';

import SolidaryQuestion from './SolidaryQuestion';

export const SolidaryNeedsQuestion = ({ label }) => {
  const { data, loading } = useGetList(
    'needs',
    { page: 1, perPage: 10 },
    { field: 'id', order: 'ASC' }
  );

  const needs = Object.values(data) || [];
  const choices = needs.map((n) => ({ id: n.id, name: n.label }));

  if (loading) {
    return <LinearProgress />;
  }

  if (needs.length === 0) {
    return null;
  }

  return (
    <SolidaryQuestion question={label}>
      <CheckboxGroupInput source="needs" label="" choices={choices} />
    </SolidaryQuestion>
  );
};
