// interface DragResult<T> {
//   removedIndex: number | null;
//   addedIndex: number | null;
//   payload: T;
// }
//
// export function applyDrag<T>(arr: T[], dragResult: DragResult<T>): T[] {
//   const { removedIndex, addedIndex, payload } = dragResult;
//   if (removedIndex === null && addedIndex === null) return arr;
//
//   const result = [...arr];
//   let itemToAdd = payload;
//
//   if (removedIndex !== null) {
//     itemToAdd = result.splice(removedIndex, 1)[0];
//   }
//
//   if (addedIndex !== null) {
//     result.splice(addedIndex, 0, itemToAdd);
//   }
//
//   return result;
// }

// export const applyDrag = (arr, dragResult) => {
//   console.log(dragResult)
//   const { removedIndex, addedIndex, payload } = dragResult;
//   if (removedIndex === null && addedIndex === null) return arr;

//   const result = [...arr];
//   let itemToAdd = payload;

//   if (removedIndex !== null) {
//     itemToAdd = result.splice(removedIndex, 1)[0];
//   }

//   if (addedIndex !== null) {
//     result.splice(addedIndex, 0, itemToAdd);
//   }

//   return result;
// };

// export const generateItems = (count, creator) => {
//   const result = [];
//   for (let i = 0; i < count; i++) {
//     result.push(creator(i));
//   }
//   return result;
// };

// utils.js

// Apply the drag result to the items array
export function applyDrag(items: App.Data.TaskData[], dropResult: any) {

  const { removedIndex, addedIndex, payload } = dropResult;

  if (removedIndex !== null && addedIndex !== null) {

    const updatedItems = [...items];

    updatedItems.splice(removedIndex, 1);

    updatedItems.splice(addedIndex, 0, payload);

    return updatedItems;

  }

  return items;

}
