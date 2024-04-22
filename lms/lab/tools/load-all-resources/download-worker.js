onmessage = async function (event) {
  const { page, itemPerPages, data, baseUrl } = event["data"];
  const start = (page - 1) * itemPerPages;
  let end = itemPerPages * page;

  if (end >= data.length) {
    end = data.length;
  }

  const promises = [];
  for (let i = start; i < end; i++) {
    promises.push(this.fetch(`${baseUrl}/${data[i]}`));
  }
  await Promise.allSettled(promises);

  this.postMessage({
    completed: promises.length,
  });
};
