import React from 'react';

export const ReferenceRecordIdMapper = ({ children, attribute, record, ...props }) => {
  if (!record[attribute]) {
    return null;
  }

  return React.cloneElement(children, {
    ...props,
    record: {
      ...record,
      [attribute]: record[attribute].map((r) => r.id),
    },
  });
};
