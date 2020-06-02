import React from 'react';
import { CheckboxGroupInput, useGetList } from 'react-admin';
import { LinearProgress } from '@material-ui/core';

const SolidaryNeeds = () => {
  const { data } = useGetList('needs', { page: 1, perPage: 10 }, { field: 'id', order: 'ASC' });
  const needs = Object.values(data) || [];
  const choices = needs.map((n) => ({ id: n.id, name: n.label }));

  if (!needs.length) {
    return <LinearProgress />;
  }

  return <CheckboxGroupInput source="needs" label="" choices={choices} />;
};

export default SolidaryNeeds;
