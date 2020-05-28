import React from 'react';
import {  Box } from '@material-ui/core';

const SolidaryQuestion = ({ question, children }) => (
  <Box display="flex" flexDirection="column" mb="2rem" p={2} boxShadow={1}>
    <Box mb={1} fontWeight="fontWeightBold">
      {question}
    </Box>
    {children}
  </Box>
);

export default SolidaryQuestion;
